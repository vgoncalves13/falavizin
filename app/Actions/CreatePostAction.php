<?php

namespace App\Actions;

use App\Enums\PointEventReason;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewContentNotification;
use App\Notifications\NewRequestNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class CreatePostAction
{
    public function execute(
        User $user,
        array $data,
        ?TemporaryUploadedFile $image = null,
        ?Carbon $eventStartsAt = null,
        ?Carbon $eventEndsAt = null,
        ?array $pollData = null,
    ): Post {
        $imagePath = null;

        if ($image) {
            $imagePath = $image->store('posts', 'public');
        }

        try {
            $post = DB::transaction(function () use ($user, $data, $imagePath, $eventStartsAt, $eventEndsAt, $pollData): Post {
                $post = $user->posts()->create([
                    'category_id' => $data['category_id'],
                    'service_category_id' => $data['service_category_id'] ?? null,
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'location' => $data['location'] ?? null,
                    'image' => $imagePath,
                    'event_starts_at' => $eventStartsAt,
                    'event_ends_at' => $eventEndsAt,
                    'status' => PostStatus::Pending,
                ]);

                if ($pollData && ! empty($pollData['question']) && ! empty($pollData['options'])) {
                    (new CreatePollAction)->execute(
                        post: $post,
                        question: $pollData['question'],
                        options: array_filter($pollData['options']),
                        endsAt: isset($pollData['ends_at']) ? Carbon::parse($pollData['ends_at']) : null,
                    );

                    (new AwardPointsAction)->execute($user, PointEventReason::PollCreated, $post);
                }

                (new AwardPointsAction)->execute($user, PointEventReason::PostCreated, $post);

                return $post;
            });
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        $this->notifyAdmins($post->title);
        $this->notifyMerchants($post);

        return $post;
    }

    private function notifyAdmins(string $title): void
    {
        $admins = User::where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new NewContentNotification('post', $title));
        Cache::forget('admin:moderation_count');
    }

    private function notifyMerchants(Post $post): void
    {
        if (! $post->service_category_id) {
            return;
        }

        $isPedido = Category::where('id', $post->category_id)
            ->where('slug', 'pedido')
            ->exists();

        if (! $isPedido) {
            return;
        }

        $merchants = User::query()
            ->whereHas('businesses', function ($q) use ($post) {
                $q->whereHas('categories', function ($cq) use ($post) {
                    $cq->where('categories.id', $post->service_category_id);
                });
            })
            ->where('id', '!=', $post->user_id)
            ->get();

        if ($merchants->isEmpty()) {
            return;
        }

        Notification::send($merchants, new NewRequestNotification($post));
    }
}

<?php

namespace App\Actions;

use App\Enums\PointEventReason;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewContentNotification;
use App\Notifications\NewRequestNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class CreatePostAction
{
    public function execute(
        User $user,
        Neighborhood $neighborhood,
        array $data,
        array $images = [],
        ?Carbon $eventStartsAt = null,
        ?Carbon $eventEndsAt = null,
        ?array $pollData = null,
    ): Post {
        throw_unless($neighborhood->is_active, ValidationException::withMessages([
            'title' => 'Este bairro não está mais ativo.',
        ]));

        $rateKey = "create-post:{$user->getKey()}";
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            throw ValidationException::withMessages(['title' => 'Aguarde antes de publicar novamente.']);
        }

        $duplicateExists = $user->posts()->withTrashed()
            ->where('title', $data['title'])
            ->where('body', $data['body'])
            ->where('created_at', '>=', now()->subMinutes(15))
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages(['title' => 'Esta publicação já foi enviada recentemente.']);
        }

        $imagePaths = [];

        try {
            foreach ($images as $image) {
                throw_unless($image instanceof TemporaryUploadedFile);
                $imagePaths[] = $image->store('posts', 'public');
            }

            $post = DB::transaction(function () use ($user, $neighborhood, $data, $imagePaths, $eventStartsAt, $eventEndsAt, $pollData): Post {
                $post = $user->posts()->create([
                    'category_id' => $data['category_id'],
                    'service_category_id' => $data['service_category_id'] ?? null,
                    'neighborhood_id' => $neighborhood->id,
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'location' => $data['location'] ?? null,
                    'image' => $imagePaths[0] ?? null,
                    'images' => $imagePaths ?: null,
                    'event_starts_at' => $eventStartsAt,
                    'event_ends_at' => $eventEndsAt,
                    'status' => PostStatus::Approved,
                    'approved_at' => now(),
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
            if ($imagePaths !== []) {
                Storage::disk('public')->delete($imagePaths);
            }

            throw $exception;
        }

        RateLimiter::hit($rateKey, 600);

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
                $q->where('neighborhood_id', $post->neighborhood_id)
                    ->whereHas('categories', function ($cq) use ($post) {
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

<?php

namespace App\Actions;

use App\Enums\PointEventReason;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

        $post = $user->posts()->create([
            'category_id' => $data['category_id'],
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

        $this->notifyAdmins($post->title);

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
}

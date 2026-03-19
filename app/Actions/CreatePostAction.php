<?php

namespace App\Actions;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class CreatePostAction
{
    public function execute(User $user, array $data): Post
    {
        $post = $user->posts()->create([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'body' => $data['body'],
            'location' => $data['location'] ?? null,
            'status' => PostStatus::Pending,
        ]);

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

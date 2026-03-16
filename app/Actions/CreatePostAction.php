<?php

namespace App\Actions;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

class CreatePostAction
{
    public function execute(User $user, array $data): Post
    {
        return $user->posts()->create([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'body' => $data['body'],
            'location' => $data['location'] ?? null,
            'status' => PostStatus::Approved,
            'approved_at' => now(),
        ]);
    }
}

<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function view(?User $user, Post $post): bool
    {
        return $post->status === PostStatus::Approved
            || $user?->id === $post->user_id
            || ($user?->is_admin ?? false);
    }

    public function interact(User $user, Post $post): bool
    {
        return $post->acceptsCommunityInteractions();
    }

    public function update(User $user, Post $post): bool
    {
        if (! $post->acceptsCommunityInteractions() && ! $user->is_admin) {
            return false;
        }

        return $user->id === $post->user_id || $user->is_admin;
    }

    public function delete(User $user, Post $post): bool
    {
        if (! $post->acceptsCommunityInteractions() && ! $user->is_admin) {
            return false;
        }

        return $user->id === $post->user_id || $user->is_admin;
    }
}

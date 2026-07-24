<?php

namespace App\Policies;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function view(?User $user, Business $business): bool
    {
        return $business->status === BusinessStatus::Approved
            || $user?->id === $business->user_id
            || ($user?->is_admin ?? false);
    }

    public function interact(User $user, Business $business): bool
    {
        return $business->acceptsCommunityInteractions();
    }

    public function update(User $user, Business $business): bool
    {
        if (! $business->acceptsCommunityInteractions() && ! $user->is_admin) {
            return false;
        }

        return $user->id === $business->user_id || $user->is_admin;
    }

    public function delete(User $user, Business $business): bool
    {
        if (! $business->acceptsCommunityInteractions() && ! $user->is_admin) {
            return false;
        }

        return $user->id === $business->user_id || $user->is_admin;
    }
}

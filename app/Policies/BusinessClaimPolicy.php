<?php

namespace App\Policies;

use App\Models\BusinessClaim;
use App\Models\User;

class BusinessClaimPolicy
{
    public function create(User $user, ?BusinessClaim $claim = null): bool
    {
        return true;
    }

    public function moderate(User $user): bool
    {
        return $user->isAdministrator() || $user->isModerator();
    }
}

<?php

namespace App\Actions;

use App\Models\Business;
use App\Models\User;

class ClaimBusinessAction
{
    public function execute(Business $business, User $user): void
    {
        $business->update([
            'user_id' => $user->id,
            'claimed' => true,
            'claimed_at' => now(),
            'claim_token' => null,
        ]);
    }
}

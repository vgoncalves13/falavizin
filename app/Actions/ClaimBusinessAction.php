<?php

namespace App\Actions;

use App\Enums\PointEventReason;
use App\Models\Business;
use App\Models\User;
use App\Notifications\BusinessClaimedNotification;
use Illuminate\Support\Facades\Notification;

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

        (new AwardPointsAction)->execute($user, PointEventReason::BusinessClaimed, $business);

        $admins = User::where('is_admin', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new BusinessClaimedNotification($business, $user));
        }
    }
}

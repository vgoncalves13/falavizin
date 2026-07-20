<?php

namespace App\Actions;

use App\Enums\PointEventReason;
use App\Models\Business;
use App\Notifications\ContentModerationNotification;

class ClaimBusinessAction
{
    public function execute(Business $business, bool $approved): void
    {
        $user = $business->claimUser()->firstOrFail();

        $business->update([
            'user_id' => $approved ? $user->id : $business->user_id,
            'claimed' => $approved,
            'claimed_at' => $approved ? now() : null,
            'claim_user_id' => null,
            'claim_requested_at' => null,
        ]);

        if ($approved) {
            (new AwardPointsAction)->execute($user, PointEventReason::BusinessClaimed, $business);
        }

        $user->notify(new ContentModerationNotification(
            type: 'business',
            title: 'Reivindicação de '.$business->name,
            decision: $approved ? 'approved' : 'rejected',
            url: $approved ? route('businesses.show', $business) : null,
        ));
    }
}

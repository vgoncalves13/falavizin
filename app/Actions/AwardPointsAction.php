<?php

namespace App\Actions;

use App\Enums\PointEventReason;
use App\Models\PointEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AwardPointsAction
{
    public function execute(User $user, PointEventReason $reason, ?Model $pointable = null): void
    {
        PointEvent::create([
            'user_id' => $user->id,
            'points' => $reason->points(),
            'reason' => $reason,
            'pointable_type' => $pointable ? $pointable->getMorphClass() : null,
            'pointable_id' => $pointable?->getKey(),
        ]);

        $user->increment('points', $reason->points());
    }
}

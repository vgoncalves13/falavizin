<?php

namespace App\Actions;

use App\Enums\PointEventReason;
use App\Models\PointEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AwardPointsAction
{
    public function execute(
        User $user,
        PointEventReason $reason,
        ?Model $pointable = null,
        ?string $idempotencyKey = null,
    ): void {
        $idempotencyKey ??= $pointable
            ? implode(':', [$reason->value, $pointable->getMorphClass(), $pointable->getKey()])
            : null;

        DB::transaction(function () use ($user, $reason, $pointable, $idempotencyKey): void {
            $attributes = [
                'user_id' => $user->id,
                'points' => $reason->points(),
                'reason' => $reason,
                'pointable_type' => $pointable?->getMorphClass(),
                'pointable_id' => $pointable?->getKey(),
            ];

            $event = $idempotencyKey
                ? PointEvent::firstOrCreate(['idempotency_key' => $idempotencyKey], $attributes)
                : PointEvent::create($attributes);

            if ($event->wasRecentlyCreated) {
                $user->increment('points', $reason->points());
            }
        });
    }
}

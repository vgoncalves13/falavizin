<?php

namespace App\Actions;

use App\Enums\BusinessClaimStatus;
use App\Enums\BusinessManagerRole;
use App\Enums\PointEventReason;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Models\BusinessManager;
use App\Models\ModerationLog;
use App\Models\User;
use App\Notifications\ClaimApprovedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApproveBusinessClaimAction
{
    public function execute(BusinessClaim $claim, User $reviewer): BusinessClaim
    {
        return DB::transaction(function () use ($claim, $reviewer): BusinessClaim {
            $claim = BusinessClaim::query()->lockForUpdate()->findOrFail($claim->id);

            if ($claim->status === BusinessClaimStatus::Approved) {
                return $claim;
            }

            if ($claim->status !== BusinessClaimStatus::Pending) {
                throw new \RuntimeException('Esta reivindicação não está pendente.');
            }

            $claim->update([
                'status' => BusinessClaimStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $business = $claim->business()->lockForUpdate()->firstOrFail();

            BusinessManager::updateOrCreate(
                ['business_id' => $business->id, 'user_id' => $claim->user_id],
                [
                    'role' => BusinessManagerRole::Owner,
                    'granted_by' => $reviewer->id,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ],
            );

            $business->update([
                'user_id' => $claim->user_id,
                'claimed' => true,
                'claimed_at' => $business->claimed_at ?? now(),
            ]);

            if (! $business->user_id) {
                $business->update(['user_id' => $claim->user_id]);
            }

            (new AwardPointsAction)->execute($claim->user, PointEventReason::BusinessClaimed, $business);

            return $claim;
        });
    }

    public function afterCommit(BusinessClaim $claim, User $reviewer): void
    {
        $claim->user->notify(new ClaimApprovedNotification($claim->business));

        ModerationLog::create([
            'moderatable_type' => Business::class,
            'moderatable_id' => $claim->business_id,
            'performed_by' => $reviewer->id,
            'action' => 'claim_approved',
            'previous_status' => 'pending',
            'new_status' => 'approved',
            'reason' => null,
        ]);

        Cache::forget('admin:moderation_count');
    }
}

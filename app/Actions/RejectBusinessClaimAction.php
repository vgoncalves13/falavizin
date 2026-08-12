<?php

namespace App\Actions;

use App\Enums\BusinessClaimStatus;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Models\ModerationLog;
use App\Models\User;
use App\Notifications\ClaimRejectedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RejectBusinessClaimAction
{
    public function execute(BusinessClaim $claim, User $reviewer, ?string $reason = null): BusinessClaim
    {
        DB::transaction(function () use ($claim, $reviewer, $reason): void {
            $claim = BusinessClaim::query()->lockForUpdate()->findOrFail($claim->id);

            if ($claim->status === BusinessClaimStatus::Rejected) {
                return;
            }

            if ($claim->status !== BusinessClaimStatus::Pending) {
                throw new \RuntimeException('Esta reivindicação não está pendente.');
            }

            $claim->update([
                'status' => BusinessClaimStatus::Rejected,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason ?: null,
            ]);
        });

        $claim->user->notify(new ClaimRejectedNotification($claim->business, $reason));

        ModerationLog::create([
            'moderatable_type' => Business::class,
            'moderatable_id' => $claim->business_id,
            'performed_by' => $reviewer->id,
            'action' => 'claim_rejected',
            'previous_status' => 'pending',
            'new_status' => 'rejected',
            'reason' => $reason,
        ]);

        Cache::forget('admin:moderation_count');

        return $claim->fresh();
    }
}

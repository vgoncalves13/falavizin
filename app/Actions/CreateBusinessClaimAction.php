<?php

namespace App\Actions;

use App\Enums\BusinessClaimStatus;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Models\ModerationLog;
use App\Models\User;
use App\Notifications\ClaimSubmittedNotification;
use App\Notifications\NewContentNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class CreateBusinessClaimAction
{
    public function execute(User $user, Business $business, ?string $message = null): BusinessClaim
    {
        $business = Business::query()->lockForUpdate()->findOrFail($business->getKey());

        $this->ensureClaimable($business, $user);

        $claim = DB::transaction(function () use ($business, $user, $message): BusinessClaim {
            return BusinessClaim::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'status' => BusinessClaimStatus::Pending,
                'message' => $message ?: null,
            ]);
        });

        ModerationLog::create([
            'moderatable_type' => Business::class,
            'moderatable_id' => $business->id,
            'performed_by' => $user->id,
            'action' => 'claim_requested',
            'previous_status' => null,
            'new_status' => 'pending',
            'reason' => null,
        ]);

        Notification::send(
            User::where('is_admin', true)->get(),
            new NewContentNotification('claim', $business->name),
        );

        $user->notify(new ClaimSubmittedNotification($business));

        Cache::forget('admin:moderation_count');

        return $claim;
    }

    private function ensureClaimable(Business $business, User $user): void
    {
        if ($business->claimed && $business->managers()->whereKey($user->getKey())->exists()) {
            throw ValidationException::withMessages([
                'claim' => 'Você já administra este estabelecimento.',
            ]);
        }

        if ($business->claimed) {
            throw ValidationException::withMessages([
                'claim' => 'Este negócio já possui um responsável.',
            ]);
        }

        $hasPending = BusinessClaim::query()
            ->where('business_id', $business->id)
            ->where('status', BusinessClaimStatus::Pending)
            ->exists();

        if ($hasPending) {
            $mine = $business->claims()->where('user_id', $user->id)->where('status', BusinessClaimStatus::Pending)->exists();

            throw ValidationException::withMessages([
                'claim' => $mine
                    ? 'Sua solicitação já está aguardando análise.'
                    : 'Este negócio já possui uma solicitação em análise.',
            ]);
        }
    }
}

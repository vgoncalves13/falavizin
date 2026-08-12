<?php

namespace App\Actions;

use App\Models\Business;
use App\Models\ModerationLog;
use App\Services\BusinessFounderEligibility;
use Illuminate\Support\Facades\DB;

class GrantFounderStatusAction
{
    public function execute(Business $business, int $performedBy): bool
    {
        $eligibility = new BusinessFounderEligibility;

        $granted = DB::transaction(function () use ($business, $eligibility): bool {
            $business = Business::query()->lockForUpdate()->findOrFail($business->id);

            if ($business->is_founder) {
                return false;
            }

            $steps = $business->onboardingSteps()
                ->pluck('step')
                ->map(fn ($step) => $step instanceof \BackedEnum ? $step->value : $step);

            $onboarding = [
                'basicDetails' => $steps->contains('basic_details'),
                'openingHours' => $steps->contains('opening_hours'),
                'ownPhoto' => $this->hasOwnPhoto($business),
                'productsServices' => $steps->contains('products_services'),
                'initialAction' => $steps->contains('initial_action'),
            ];

            if (! $eligibility->isEligible($business, $onboarding)) {
                return false;
            }

            $business->update([
                'is_founder' => true,
                'founder_granted_at' => now(),
            ]);

            return true;
        });

        if ($granted) {
            ModerationLog::create([
                'moderatable_type' => Business::class,
                'moderatable_id' => $business->id,
                'performed_by' => $performedBy,
                'action' => 'founder_granted',
                'previous_status' => null,
                'new_status' => 'founder',
                'reason' => null,
            ]);
        }

        return $granted;
    }

    private function hasOwnPhoto(Business $business): bool
    {
        return $business->photos()
            ->whereNotNull('uploaded_by')
            ->whereHas('uploader', fn ($q) => $q->whereIn('id', $business->managers()->pluck('users.id')))
            ->exists();
    }
}

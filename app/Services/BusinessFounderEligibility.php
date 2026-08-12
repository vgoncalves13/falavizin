<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class BusinessFounderEligibility
{
    public function isEnabled(): bool
    {
        return (bool) Setting::get('founder_program_enabled', false);
    }

    public function eligibleNeighborhoodId(): ?int
    {
        $value = Setting::get('founder_neighborhood_id');

        return $value ? (int) $value : null;
    }

    public function startsAt(): ?Carbon
    {
        $value = Setting::get('founder_program_starts_at');

        return $value ? now()->parse($value) : null;
    }

    public function endsAt(): ?Carbon
    {
        $value = Setting::get('founder_program_ends_at');

        return $value ? now()->parse($value) : null;
    }

    public function maxParticipants(): int
    {
        return (int) Setting::get('founder_max_participants', 0);
    }

    public function withinPeriod(): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $now = now();

        if ($this->startsAt() && $now->lt($this->startsAt())) {
            return false;
        }

        if ($this->endsAt() && $now->gt($this->endsAt())) {
            return false;
        }

        return true;
    }

    public function canFillSlot(): bool
    {
        $max = $this->maxParticipants();

        if ($max <= 0) {
            return true;
        }

        $granted = Business::where('is_founder', true)->count();

        return $granted < $max;
    }

    /**
     * @param  array{basicDetails: bool, openingHours: bool, ownPhoto: bool, productsServices: bool, initialAction: bool}  $onboarding
     */
    public function isEligible(Business $business, array $onboarding): bool
    {
        if ($business->is_founder) {
            return true;
        }

        if (! $this->withinPeriod()) {
            return false;
        }

        if ($this->eligibleNeighborhoodId() && $business->neighborhood_id !== $this->eligibleNeighborhoodId()) {
            return false;
        }

        if (! $onboarding['basicDetails'] || ! $onboarding['openingHours']) {
            return false;
        }

        if (! $onboarding['ownPhoto'] || ! $onboarding['productsServices'] || ! $onboarding['initialAction']) {
            return false;
        }

        return $this->canFillSlot();
    }
}

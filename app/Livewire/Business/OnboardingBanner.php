<?php

namespace App\Livewire\Business;

use App\Enums\BusinessOnboardingStep;
use App\Models\Business;
use App\Services\BusinessOnboardingProgress;
use Livewire\Component;

class OnboardingBanner extends Component
{
    public Business $business;

    private const DISMISSAL_COOLDOWN_HOURS = 24;

    public function dismiss(): void
    {
        session()->put("onboarding_dismissed_at_{$this->business->id}", now()->timestamp);
    }

    private function isDismissed(): bool
    {
        $dismissedAt = session()->get("onboarding_dismissed_at_{$this->business->id}");

        if (! $dismissedAt) {
            return false;
        }

        return now()->timestamp - (int) $dismissedAt < self::DISMISSAL_COOLDOWN_HOURS * 3600;
    }

    public function render()
    {
        $user = auth()->user();

        if (! $user || ! $user->can('update', $this->business)) {
            return view('livewire.business.onboarding-banner', ['show' => false]);
        }

        $progress = new BusinessOnboardingProgress;

        $show = ! $progress->isComplete($this->business) && ! $this->isDismissed();

        return view('livewire.business.onboarding-banner', [
            'show' => $show,
            'percent' => $progress->progress($this->business),
            'completed' => $progress->completedSteps($this->business),
            'total' => count(BusinessOnboardingStep::ordered()),
            'next' => $progress->nextStep($this->business),
        ]);
    }
}

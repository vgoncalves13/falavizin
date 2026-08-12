<?php

namespace App\Livewire\Business;

use App\Enums\BusinessOnboardingStep;
use App\Models\Business;
use App\Services\BusinessOnboardingProgress;
use Livewire\Component;

class OnboardingCards extends Component
{
    private const DISMISSAL_COOLDOWN_HOURS = 24;

    public function dismiss(int $businessId): void
    {
        session()->put("onboarding_dismissed_at_{$businessId}", now()->timestamp);
    }

    private function isDismissed(int $businessId): bool
    {
        $dismissedAt = session()->get("onboarding_dismissed_at_{$businessId}");

        if (! $dismissedAt) {
            return false;
        }

        return now()->timestamp - (int) $dismissedAt < self::DISMISSAL_COOLDOWN_HOURS * 3600;
    }

    public function render()
    {
        $progress = new BusinessOnboardingProgress;

        $businesses = auth()->user()
            ->managedBusinesses()
            ->with(['localNeighborhood'])
            ->get()
            ->filter(function (Business $business) use ($progress): bool {
                if ($progress->isComplete($business)) {
                    return false;
                }

                return ! $this->isDismissed($business->id);
            })
            ->map(fn (Business $business) => [
                'business' => $business,
                'percent' => $progress->progress($business),
                'completed' => $progress->completedSteps($business),
                'total' => count(BusinessOnboardingStep::ordered()),
                'next' => $progress->nextStep($business),
            ])
            ->values();

        return view('livewire.business.onboarding-cards', ['items' => $businesses]);
    }
}

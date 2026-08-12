<?php

namespace App\Services;

use App\Enums\BusinessOnboardingStep;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Carbon;

class BusinessOnboardingProgress
{
    /** @return array{step: BusinessOnboardingStep, completed: bool, completed_at: Carbon|null, completed_by: int|null} */
    public function stepStatus(Business $business, BusinessOnboardingStep $step): array
    {
        return match ($step) {
            BusinessOnboardingStep::BasicDetails => $this->basicDetailsStatus($business),
            BusinessOnboardingStep::OpeningHours => $this->openingHoursStatus($business),
            BusinessOnboardingStep::OwnPhoto => $this->ownPhotoStatus($business),
            BusinessOnboardingStep::ProductsServices => $this->productsServicesStatus($business),
            BusinessOnboardingStep::InitialAction => $this->initialActionStatus($business),
        };
    }

    /** @return array{completed: bool, completed_at: Carbon|null, completed_by: int|null} */
    private function recorded(Business $business, BusinessOnboardingStep $step): array
    {
        $record = $business->onboardingSteps()->where('step', $step->value)->first();

        return [
            'completed' => (bool) $record?->completed_at,
            'completed_at' => $record?->completed_at,
            'completed_by' => $record?->completed_by,
        ];
    }

    /** @return array{step: BusinessOnboardingStep, completed: bool, completed_at: Carbon|null, completed_by: int|null} */
    private function basicDetailsStatus(Business $business): array
    {
        return ['step' => BusinessOnboardingStep::BasicDetails, ...$this->recorded($business, BusinessOnboardingStep::BasicDetails)];
    }

    /** @return array{step: BusinessOnboardingStep, completed: bool, completed_at: Carbon|null, completed_by: int|null} */
    private function openingHoursStatus(Business $business): array
    {
        $record = $this->recorded($business, BusinessOnboardingStep::OpeningHours);

        // Horários importados não confirmam a etapa: exige confirmação explícita.
        return ['step' => BusinessOnboardingStep::OpeningHours, ...$record];
    }

    /** @return array{step: BusinessOnboardingStep, completed: bool, completed_at: Carbon|null, completed_by: int|null} */
    private function ownPhotoStatus(Business $business): array
    {
        $uploadedByManager = $business->photos()
            ->whereNotNull('uploaded_by')
            ->whereHas('uploader', fn ($q) => $q->whereIn('id', $business->managers()->pluck('users.id')))
            ->latest()
            ->first();

        return [
            'step' => BusinessOnboardingStep::OwnPhoto,
            'completed' => (bool) $uploadedByManager,
            'completed_at' => $uploadedByManager?->created_at,
            'completed_by' => $uploadedByManager?->uploaded_by,
        ];
    }

    /** @return array{step: BusinessOnboardingStep, completed: bool, completed_at: Carbon|null, completed_by: int|null} */
    private function productsServicesStatus(Business $business): array
    {
        return ['step' => BusinessOnboardingStep::ProductsServices, ...$this->recorded($business, BusinessOnboardingStep::ProductsServices)];
    }

    /** @return array{step: BusinessOnboardingStep, completed: bool, completed_at: Carbon|null, completed_by: int|null} */
    private function initialActionStatus(Business $business): array
    {
        $record = $this->recorded($business, BusinessOnboardingStep::InitialAction);

        return ['step' => BusinessOnboardingStep::InitialAction, ...$record];
    }

    /** @return list<array{step: BusinessOnboardingStep, completed: bool}> */
    public function steps(Business $business): array
    {
        return array_map(
            fn (BusinessOnboardingStep $step): array => $this->stepStatus($business, $step),
            BusinessOnboardingStep::ordered(),
        );
    }

    public function progress(Business $business): int
    {
        $steps = BusinessOnboardingStep::ordered();
        $completed = collect($this->steps($business))->filter(fn (array $status): bool => $status['completed'])->count();

        return (int) round(($completed / count($steps)) * 100);
    }

    public function completedSteps(Business $business): int
    {
        return collect($this->steps($business))->filter(fn (array $status): bool => $status['completed'])->count();
    }

    public function isComplete(Business $business): bool
    {
        return collect($this->steps($business))->every(fn (array $status): bool => $status['completed']);
    }

    public function nextStep(Business $business): ?BusinessOnboardingStep
    {
        foreach (BusinessOnboardingStep::ordered() as $step) {
            if (! $this->stepStatus($business, $step)['completed']) {
                return $step;
            }
        }

        return null;
    }

    public function completeStep(Business $business, BusinessOnboardingStep $step, User $user, ?array $data = null): void
    {
        $business->onboardingSteps()->updateOrCreate(
            ['step' => $step->value],
            ['completed_at' => now(), 'completed_by' => $user->id, 'data' => $data],
        );
    }

    public function resetStep(Business $business, BusinessOnboardingStep $step): void
    {
        $business->onboardingSteps()->where('step', $step->value)->delete();
    }
}

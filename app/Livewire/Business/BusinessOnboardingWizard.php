<?php

namespace App\Livewire\Business;

use App\Actions\GrantFounderStatusAction;
use App\Enums\BusinessOnboardingStep;
use App\Models\Business;
use App\Services\BusinessOnboardingProgress;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\WithFileUploads;

class BusinessOnboardingWizard extends Component
{
    use WithFileUploads;

    public Business $business;

    public string $currentStep = '';

    /** @var array<int, array{day: string, open: string, close: string, closed: bool}> */
    public array $openingHours = [];

    public string $description = '';

    /** @var array<int, mixed> */
    public array $newPhotos = [];

    private const WEEK_DAYS = [
        'Segunda-feira', 'Terça-feira', 'Quarta-feira',
        'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo',
    ];

    public function mount(string $step = ''): void
    {
        Gate::authorize('update', $this->business);

        $progress = new BusinessOnboardingProgress;
        $next = $step && in_array($step, array_column(BusinessOnboardingStep::cases(), 'value'), true)
            ? $step
            : $progress->nextStep($this->business)?->value;

        $this->currentStep = $next ?? '';
        $this->description = $this->business->description ?? '';
        $this->openingHours = $this->initOpeningHours($this->business->opening_hours);
    }

    /** @param  array<int, array{day: string, open: string, close: string, closed: bool}>|null  $stored */
    private function initOpeningHours(?array $stored): array
    {
        $byDay = [];
        foreach ($stored ?? [] as $row) {
            if (isset($row['day'])) {
                $byDay[$row['day']] = $row;
            }
        }

        return array_map(fn (string $day) => [
            'day' => $day,
            'open' => $byDay[$day]['open'] ?? '08:00',
            'close' => $byDay[$day]['close'] ?? '18:00',
            'closed' => (bool) ($byDay[$day]['closed'] ?? true),
        ], self::WEEK_DAYS);
    }

    public function gotoStep(string $step): void
    {
        if (in_array($step, array_column(BusinessOnboardingStep::cases(), 'value'), true)) {
            $this->currentStep = $step;
        }
    }

    public function confirmBasicDetails(): void
    {
        Gate::authorize('update', $this->business);

        $snapshot = [
            'name' => $this->business->name,
            'description' => $this->business->description,
            'address' => $this->business->address,
            'phone' => $this->business->phone,
            'whatsapp' => $this->business->whatsapp,
            'instagram' => $this->business->instagram,
            'website' => $this->business->website,
        ];

        (new BusinessOnboardingProgress)->completeStep(
            $this->business,
            BusinessOnboardingStep::BasicDetails,
            auth()->user(),
            $snapshot,
        );

        $this->advanceAfter('Dados básicos confirmados.');
    }

    public function saveOpeningHours(): void
    {
        Gate::authorize('update', $this->business);

        $this->validate([
            'openingHours.*.open' => ['nullable', 'date_format:H:i'],
            'openingHours.*.close' => ['nullable', 'date_format:H:i'],
        ]);

        $this->business->update(['opening_hours' => $this->buildOpeningHours()]);

        (new BusinessOnboardingProgress)->completeStep(
            $this->business,
            BusinessOnboardingStep::OpeningHours,
            auth()->user(),
        );

        $this->advanceAfter('Horários confirmados.');
    }

    /** @return array<int, array{day: string, open: string, close: string, closed: bool}>|null */
    private function buildOpeningHours(): ?array
    {
        $hasAnyOpen = collect($this->openingHours)->contains(fn ($h) => ! ($h['closed'] ?? true));

        if (! $hasAnyOpen) {
            return null;
        }

        return array_map(fn (array $h) => [
            'day' => $h['day'],
            'open' => $h['closed'] ? '' : ($h['open'] ?? ''),
            'close' => $h['closed'] ? '' : ($h['close'] ?? ''),
            'closed' => (bool) ($h['closed'] ?? true),
        ], $this->openingHours);
    }

    public function saveServices(): void
    {
        Gate::authorize('update', $this->business);

        $this->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $this->business->update(['description' => $this->description]);

        (new BusinessOnboardingProgress)->completeStep(
            $this->business,
            BusinessOnboardingStep::ProductsServices,
            auth()->user(),
            ['description' => $this->description],
        );

        $this->advanceAfter('Produtos e serviços informados.');
    }

    public function completeAction(string $key): Redirector|bool
    {
        Gate::authorize('update', $this->business);

        if ($key === 'share') {
            session()->flash('highlight_share', $this->business->id);

            return redirect()->to($this->business->canonicalUrl());
        }

        return match ($key) {
            'qr' => redirect()->to(route('businesses.qr', $this->business)),
            'news', 'promotion', 'event' => redirect()->to($this->redirectForAction($key)),
            default => false,
        };
    }

    private function redirectForAction(string $key): string
    {
        return match ($key) {
            'news' => route('neighborhood.feed.create', $this->business->localNeighborhood->routeParameters()),
            'event' => route('neighborhood.feed.create', $this->business->localNeighborhood->routeParameters()),
            default => $this->business->canonicalUrl(),
        };
    }

    public function uploadPhotos(): void
    {
        Gate::authorize('update', $this->business);

        $this->validate([
            'newPhotos.*' => ['image', 'max:5120'],
        ]);

        $manager = new PhotoGallery;
        $manager->business = $this->business;
        $manager->newPhotos = $this->newPhotos;
        $manager->savePhotos();

        $this->newPhotos = [];
        $this->business->unsetRelation('photos');
        $this->advanceAfter('Foto adicionada.');
    }

    private function advanceAfter(string $message): void
    {
        (new GrantFounderStatusAction)->execute($this->business, auth()->id());

        $next = (new BusinessOnboardingProgress)->nextStep($this->business);

        session()->flash('onboarding_flash', $message);

        $this->redirect(
            $next
                ? route('businesses.onboarding', ['business' => $this->business, 'step' => $next->value])
                : $this->business->canonicalUrl(),
        );
    }

    public function render()
    {
        $progress = new BusinessOnboardingProgress;
        $steps = $progress->steps($this->business);
        $percent = $progress->progress($this->business);
        $completedCount = $progress->completedSteps($this->business);

        return view('livewire.business.business-onboarding-wizard', [
            'steps' => $steps,
            'percent' => $percent,
            'completedCount' => $completedCount,
        ]);
    }
}

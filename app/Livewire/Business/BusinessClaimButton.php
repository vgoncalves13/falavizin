<?php

namespace App\Livewire\Business;

use App\Actions\CreateBusinessClaimAction;
use App\Enums\BusinessClaimStatus;
use App\Models\Business;
use App\Models\BusinessClaim;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class BusinessClaimButton extends Component
{
    public Business $business;

    public bool $showForm = false;

    public bool $justSubmitted = false;

    public bool $confirm = false;

    public string $message = '';

    public ?BusinessClaim $pendingClaim = null;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->pendingClaim = $this->business->claims()
                ->where('user_id', auth()->id())
                ->where('status', BusinessClaimStatus::Pending)
                ->first();
        }
    }

    public function start(): Redirector|bool
    {
        if (auth()->guest()) {
            return redirect()->guest(route('login'));
        }

        Gate::authorize('interact', $this->business);

        $this->showForm = true;

        return false;
    }

    public function submit(CreateBusinessClaimAction $action): void
    {
        $this->validate([
            'confirm' => ['required', 'accepted'],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [
            'confirm.required' => 'Confirme que você representa este estabelecimento.',
            'confirm.accepted' => 'Confirme que você representa este estabelecimento.',
        ]);

        try {
            $action->execute(auth()->user(), $this->business, $this->message ?: null);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors());

            return;
        }

        $this->pendingClaim = $this->business->claims()
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        $this->showForm = false;
        $this->justSubmitted = true;
        $this->reset('confirm', 'message');
        $this->dispatch('claim-submitted');
    }

    public function render()
    {
        $canManage = auth()->check() && auth()->user()->can('update', $this->business);

        return view('livewire.business.business-claim-button', compact('canManage'));
    }
}

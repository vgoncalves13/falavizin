<?php

namespace App\Livewire\Business;

use App\Actions\CreatePromotionAction;
use App\Actions\UpdatePromotionAction;
use App\Http\Requests\StorePromotionRequest;
use App\Models\Business;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class PromotionForm extends Component
{
    public Business $business;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $startsAt = '';

    public string $endsAt = '';

    protected function rules(): array
    {
        return StorePromotionRequest::rulesFor(
            startsAt: 'startsAt',
            endsAt: 'endsAt',
            editing: (bool) $this->editingId,
        );
    }

    protected function messages(): array
    {
        return StorePromotionRequest::messagesFor(
            startsAt: 'startsAt',
            endsAt: 'endsAt',
        );
    }

    #[On('edit-promotion')]
    public function startEdit(int $id): void
    {
        Gate::authorize('update', $this->business);
        $promotion = $this->business->promotions()->findOrFail($id);

        $this->editingId = $promotion->id;
        $this->title = $promotion->title;
        $this->description = $promotion->description ?? '';
        $this->startsAt = $promotion->starts_at?->format('Y-m-d') ?? '';
        $this->endsAt = $promotion->ends_at?->format('Y-m-d') ?? '';
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'title', 'description', 'startsAt', 'endsAt');
    }

    public function save(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        Gate::authorize('update', $this->business);

        $this->validate();

        if ($this->editingId) {
            $promotion = $this->business->promotions()->findOrFail($this->editingId);

            (new UpdatePromotionAction)->execute($promotion, [
                'title' => $this->title,
                'description' => $this->description ?: null,
                'starts_at' => $this->startsAt ?: null,
                'ends_at' => $this->endsAt ?: null,
            ]);
        } else {
            (new CreatePromotionAction)->execute($this->business, [
                'title' => $this->title,
                'description' => $this->description ?: null,
                'starts_at' => $this->startsAt ?: null,
                'ends_at' => $this->endsAt ?: null,
            ]);
        }

        $isNew = ! $this->editingId;

        $this->reset('editingId', 'title', 'description', 'startsAt', 'endsAt');

        session()->flash(
            'success',
            $isNew
                ? 'Promoção enviada! Ela aparecerá aqui após aprovação.'
                : 'Promoção atualizada com sucesso!'
        );

        $this->redirect(route('neighborhood.businesses.show', [
            ...$this->business->localNeighborhood->routeParameters(),
            'business' => $this->business,
        ]));
    }

    public function render()
    {
        return view('livewire.business.promotion-form');
    }
}

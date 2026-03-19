<?php

namespace App\Livewire\Business;

use App\Actions\CreatePromotionAction;
use App\Models\Business;
use App\Models\Promotion;
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
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'startsAt' => ['nullable', 'date', 'before_or_equal:endsAt'],
            'endsAt' => ['nullable', 'date', $this->editingId ? 'nullable' : 'after_or_equal:today'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'O título da promoção é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 5 caracteres.',
            'endsAt.after_or_equal' => 'A data de término deve ser hoje ou no futuro.',
        ];
    }

    #[On('edit-promotion')]
    public function startEdit(int $id): void
    {
        $promotion = Promotion::findOrFail($id);

        Gate::authorize('update', $promotion->business);

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

        $this->validate();

        if ($this->editingId) {
            $promotion = Promotion::findOrFail($this->editingId);
            Gate::authorize('update', $promotion->business);

            $promotion->update([
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

        $this->redirect(route('businesses.show', $this->business));
    }

    public function render()
    {
        return view('livewire.business.promotion-form');
    }
}

<?php

namespace App\Livewire\Business;

use App\Actions\CreatePromotionAction;
use App\Models\Business;
use Livewire\Component;

class PromotionForm extends Component
{
    public Business $business;

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
            'endsAt' => ['nullable', 'date', 'after_or_equal:today'],
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

    public function save(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate();

        (new CreatePromotionAction)->execute($this->business, [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'starts_at' => $this->startsAt ?: null,
            'ends_at' => $this->endsAt ?: null,
        ]);

        $this->title = '';
        $this->description = '';
        $this->startsAt = '';
        $this->endsAt = '';

        $this->dispatch('promotion-created');
    }

    public function render()
    {
        return view('livewire.business.promotion-form');
    }
}

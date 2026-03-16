<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('business'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'starts_at' => ['nullable', 'date', 'before_or_equal:ends_at'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da promoção é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 5 caracteres.',
            'ends_at.after_or_equal' => 'A data de término deve ser hoje ou no futuro.',
            'starts_at.before_or_equal' => 'A data de início deve ser antes da data de término.',
        ];
    }
}

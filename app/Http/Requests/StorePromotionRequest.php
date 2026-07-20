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
        return self::rulesFor();
    }

    public static function rulesFor(
        string $startsAt = 'starts_at',
        string $endsAt = 'ends_at',
        bool $editing = false,
    ): array {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            $startsAt => ['nullable', 'date', 'before_or_equal:'.$endsAt],
            $endsAt => ['nullable', 'date', $editing ? 'nullable' : 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return self::messagesFor();
    }

    public static function messagesFor(
        string $startsAt = 'starts_at',
        string $endsAt = 'ends_at',
    ): array {
        return [
            'title.required' => 'O título da promoção é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 5 caracteres.',
            $endsAt.'.after_or_equal' => 'A data de término deve ser hoje ou no futuro.',
            $startsAt.'.before_or_equal' => 'A data de início deve ser antes da data de término.',
        ];
    }
}

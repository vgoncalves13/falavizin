<?php

namespace App\Http\Requests;

use App\Enums\BusinessPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $business = $this->route('business');

            if ($business->plan === BusinessPlan::Featured) {
                return;
            }

            $lastPromotion = $business->promotions()
                ->withTrashed()
                ->latest()
                ->first();

            if (! $lastPromotion) {
                return;
            }

            $nextAllowedAt = $lastPromotion->created_at->addDays(7);

            if (now()->lt($nextAllowedAt)) {
                $validator->errors()->add(
                    'title',
                    'Este negócio já criou uma promoção recentemente. Próxima disponível em '
                    .$nextAllowedAt->format('d/m/Y \à\s H:i').'.'
                );
            }
        });
    }
}

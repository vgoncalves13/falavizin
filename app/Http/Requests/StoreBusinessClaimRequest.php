<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'confirm' => ['required', 'accepted'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm.required' => 'Confirme que você representa este estabelecimento.',
            'confirm.accepted' => 'Confirme que você representa este estabelecimento.',
        ];
    }
}

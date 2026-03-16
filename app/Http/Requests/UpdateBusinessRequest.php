<?php

namespace App\Http\Requests;

use App\Enums\BusinessPlan;
use App\Enums\BusinessStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('business'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'plan' => ['sometimes', new Enum(BusinessPlan::class)],
            'status' => ['sometimes', new Enum(BusinessStatus::class)],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do negócio é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'category_id.required' => 'Selecione uma categoria.',
            'neighborhood.required' => 'O bairro é obrigatório.',
            'cover_photo.image' => 'O arquivo deve ser uma imagem.',
            'cover_photo.max' => 'A imagem não pode ter mais de 5MB.',
        ];
    }
}

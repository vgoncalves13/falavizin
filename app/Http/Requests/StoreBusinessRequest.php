<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return self::rulesFor();
    }

    public static function rulesFor(
        string $category = 'category_id',
        string $phones = 'phone',
        string $openingHours = 'opening_hours',
        string $coverPhoto = 'cover_photo',
    ): array {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            $category => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            $phones => ['nullable', 'array', 'max:5'],
            $phones.'.*' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            $openingHours => ['nullable', 'array'],
            $openingHours.'.*.day' => ['required', 'string', 'max:20'],
            $openingHours.'.*.open' => ['nullable', 'date_format:H:i'],
            $openingHours.'.*.close' => ['nullable', 'date_format:H:i'],
            $openingHours.'.*.closed' => ['required', 'boolean'],
            $coverPhoto => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return self::messagesFor();
    }

    public static function messagesFor(
        string $category = 'category_id',
        string $coverPhoto = 'cover_photo',
    ): array {
        return [
            'name.required' => 'O nome do negócio é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            $category.'.required' => 'Selecione uma categoria.',
            'neighborhood.required' => 'O bairro é obrigatório.',
            $coverPhoto.'.image' => 'O arquivo deve ser uma imagem.',
            $coverPhoto.'.max' => 'A imagem não pode ter mais de 5MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone') && ! is_array($this->input('phone'))) {
            $phone = trim((string) $this->input('phone'));
            $this->merge(['phone' => $phone === '' ? null : [$phone]]);
        }
    }
}

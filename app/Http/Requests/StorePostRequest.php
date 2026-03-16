<?php

namespace App\Http\Requests;

use App\Enums\PostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', new Enum(PostStatus::class)],
        ];
    }
}

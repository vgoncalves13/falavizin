<?php

namespace App\Http\Requests;

use App\Models\Neighborhood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrimaryNeighborhoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'neighborhood_id' => [
                'required',
                'integer',
                Rule::exists('neighborhoods', 'id')->where('is_active', true),
            ],
        ];
    }

    public function neighborhood(): Neighborhood
    {
        return Neighborhood::query()
            ->active()
            ->findOrFail($this->validated('neighborhood_id'));
    }
}

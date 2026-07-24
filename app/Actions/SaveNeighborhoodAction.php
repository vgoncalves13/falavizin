<?php

namespace App\Actions;

use App\Models\Neighborhood;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveNeighborhoodAction
{
    public function execute(Neighborhood $neighborhood, array $data): Neighborhood
    {
        $data['state_code'] = strtoupper($data['state_code']);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['city_slug'] = Str::slug($data['city_slug'] ?: $data['city']);

        $this->validate($data, $neighborhood);

        $hasContent = $neighborhood->exists && (
            $neighborhood->posts()->exists() ||
            $neighborhood->businesses()->exists()
        );

        if ($hasContent) {
            unset($data['state_code'], $data['city'], $data['city_slug'], $data['slug']);
        }

        $neighborhood->fill($data)->save();

        return $neighborhood;
    }

    private function validate(array $data, Neighborhood $neighborhood): void
    {
        if (empty($data['name'])) {
            throw ValidationException::withMessages(['name' => 'O nome é obrigatório.']);
        }

        if (empty($data['city'])) {
            throw ValidationException::withMessages(['city' => 'A cidade é obrigatória.']);
        }

        if (! isset($data['state_code']) || strlen($data['state_code']) !== 2) {
            throw ValidationException::withMessages(['state_code' => 'A UF deve ter exatamente 2 caracteres.']);
        }

        if (isset($data['latitude']) && ($data['latitude'] < -90 || $data['latitude'] > 90)) {
            throw ValidationException::withMessages(['latitude' => 'A latitude deve estar entre -90 e 90.']);
        }

        if (isset($data['longitude']) && ($data['longitude'] < -180 || $data['longitude'] > 180)) {
            throw ValidationException::withMessages(['longitude' => 'A longitude deve estar entre -180 e 180.']);
        }

        if (isset($data['sort_order']) && (! is_numeric($data['sort_order']) || $data['sort_order'] < 0)) {
            throw ValidationException::withMessages(['sort_order' => 'A ordenação deve ser um inteiro não negativo.']);
        }

        $exists = Neighborhood::query()
            ->where('state_code', $data['state_code'])
            ->where('city_slug', $data['city_slug'])
            ->where('slug', $data['slug'])
            ->when($neighborhood->exists, fn ($q) => $q->where('id', '!=', $neighborhood->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'Já existe um bairro com este nome nesta cidade e UF.',
            ]);
        }
    }
}

<?php

namespace App\Actions;

use App\Models\Neighborhood;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetNeighborhoodStatusAction
{
    public function execute(Neighborhood $neighborhood, bool $active): Neighborhood
    {
        return DB::transaction(function () use ($neighborhood, $active): Neighborhood {
            $activeIds = Neighborhood::query()
                ->active()
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');

            $locked = Neighborhood::query()->lockForUpdate()->findOrFail($neighborhood->id);

            if (! $active && $locked->is_active && $activeIds->count() <= 1) {
                throw ValidationException::withMessages([
                    'status' => 'O último bairro ativo não pode ser desativado.',
                ]);
            }

            $locked->update(['is_active' => $active]);

            return $locked;
        });
    }
}

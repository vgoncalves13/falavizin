<?php

namespace App\Http\Requests;

use App\Models\Business;
use Illuminate\Support\Facades\Gate;

class UpdateBusinessRequest extends StoreBusinessRequest
{
    public function authorize(): bool
    {
        $neighborhood = $this->route('neighborhood');
        $slug = $this->route('business');

        $query = Business::query()->where('slug', $slug);
        if ($neighborhood) {
            $query->where('neighborhood_id', $neighborhood->id);
        }
        $business = $query->firstOrFail();

        return Gate::allows('update', $business);
    }
}

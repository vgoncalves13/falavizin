<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Gate;

class UpdateBusinessRequest extends StoreBusinessRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('business'));
    }
}

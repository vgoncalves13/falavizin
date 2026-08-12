<?php

namespace App\Http\Controllers;

use App\Actions\CreateBusinessClaimAction;
use App\Http\Requests\StoreBusinessClaimRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ClaimBusinessController extends Controller
{
    public function request(StoreBusinessClaimRequest $request, CreateBusinessClaimAction $action): RedirectResponse
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('business');

        $business = Business::query()
            ->where('slug', $slug)
            ->when($neighborhood, fn ($q) => $q->forNeighborhood($neighborhood))
            ->firstOrFail();

        Gate::authorize('interact', $business);

        $showRoute = $neighborhood
            ? route('neighborhood.businesses.show', [...$neighborhood->routeParameters(), 'business' => $business])
            : route('businesses.show', $business);

        try {
            $action->execute(
                user: auth()->user(),
                business: $business,
                message: $request->validated('message'),
            );
        } catch (ValidationException $exception) {
            return redirect($showRoute)->withErrors($exception->errors(), 'claim')->withInput();
        }

        return redirect($showRoute)
            ->with('success', 'Solicitação enviada! Nossa equipe analisará sua solicitação.');
    }
}

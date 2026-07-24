<?php

namespace App\Http\Controllers;

use App\Actions\UpdatePrimaryNeighborhoodAction;
use App\Http\Requests\UpdatePrimaryNeighborhoodRequest;
use App\Models\Neighborhood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NeighborhoodSelectionController extends Controller
{
    public function create(Request $request): View
    {
        $neighborhoods = Neighborhood::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $lastNeighborhood = $neighborhoods->firstWhere(
            'id',
            (int) $request->cookie('last_neighborhood_id'),
        );

        return view('neighborhoods.index', compact('neighborhoods', 'lastNeighborhood'));
    }

    public function update(UpdatePrimaryNeighborhoodRequest $request, UpdatePrimaryNeighborhoodAction $action): RedirectResponse
    {
        $neighborhood = $request->neighborhood();

        $action->execute(
            user: $request->user(),
            neighborhood: $neighborhood,
        );

        return redirect()->route('neighborhood.home', $neighborhood->routeParameters());
    }
}

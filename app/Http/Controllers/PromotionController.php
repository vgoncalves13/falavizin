<?php

namespace App\Http\Controllers;

use App\Actions\CreatePromotionAction;
use App\Http\Requests\StorePromotionRequest;
use App\Models\Business;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $neighborhood = request()->route('neighborhood');

        $promotions = Promotion::query()
            ->active()
            ->when($neighborhood, fn ($q) => $q->whereHas('business', fn ($q) => $q->forNeighborhood($neighborhood)))
            ->with('business.category')
            ->latest()
            ->paginate(12);

        return view('promotions.index', compact('neighborhood', 'promotions'));
    }

    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $neighborhood = request()->route('neighborhood');
        $business = Business::where('slug', request()->route('business'))->firstOrFail();

        (new CreatePromotionAction)->execute($business, $request->validated());

        if ($neighborhood) {
            return redirect()->route('neighborhood.businesses.show', [
                ...$neighborhood->routeParameters(),
                'business' => $business,
            ])->with('success', 'Promoção criada com sucesso!');
        }

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Promoção criada com sucesso!');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        Gate::authorize('update', $promotion->business);

        $promotion->delete();

        return redirect()->back()->with('success', 'Promoção removida.');
    }
}

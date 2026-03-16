<?php

namespace App\Http\Controllers;

use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(): View
    {
        return view('businesses.index');
    }

    public function show(Business $business): View
    {
        $business->load(['user', 'category', 'coverPhoto', 'promotions' => function ($q) {
            $q->active()->latest();
        }]);

        return view('businesses.show', compact('business'));
    }

    public function create(): View
    {
        return view('businesses.create');
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $business = (new CreateBusinessAction)->execute(
            user: auth()->user(),
            data: $request->validated(),
            coverPhoto: $request->file('cover_photo'),
        );

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Negócio cadastrado com sucesso!');
    }

    public function edit(Business $business): View
    {
        Gate::authorize('update', $business);

        $business->load(['category', 'coverPhoto']);

        return view('businesses.edit', compact('business'));
    }

    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        (new UpdateBusinessAction)->execute(
            business: $business,
            data: $request->validated(),
            coverPhoto: $request->file('cover_photo'),
        );

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Negócio atualizado com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Enums\BusinessPlan;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\User;
use App\Notifications\PlanUpgradeApprovedNotification;
use App\Notifications\PlanUpgradeRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(): View
    {
        $mapBusinesses = Cache::remember('businesses:map', 300, fn () => Business::query()
            ->where('status', 'approved')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with('category')
            ->get()
            ->map(fn (Business $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'category' => $b->category?->name,
                'neighborhood' => $b->neighborhood,
                'lat' => (float) $b->lat,
                'lng' => (float) $b->lng,
                'url' => route('businesses.show', $b),
                'featured' => $b->plan->value === 'featured',
            ])
        );

        return view('businesses.index', compact('mapBusinesses'));
    }

    public function show(Business $business): View
    {
        Gate::authorize('view', $business);

        $canManage = auth()->user()?->can('update', $business);

        $business->load(['user', 'category', 'coverPhoto', 'promotions' => function ($q) use ($canManage) {
            $canManage
                ? $q->whereIn('status', ['approved', 'pending'])->latest()
                : $q->active()->latest();
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

        return redirect()->route('businesses.index')
            ->with('success', 'Negócio enviado! Aguarda aprovação do admin.');
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

    public function requestUpgrade(Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        if ($business->plan === BusinessPlan::Featured) {
            return redirect()->route('businesses.show', $business)
                ->with('error', 'Este negócio já está no plano Destaque.');
        }

        if ($business->plan_upgrade_requested_at) {
            return redirect()->route('businesses.show', $business)
                ->with('error', 'Já existe uma solicitação de upgrade pendente.');
        }

        $business->update(['plan_upgrade_requested_at' => now()]);

        $admins = User::where('is_admin', true)->get();
        Notification::send($admins, new PlanUpgradeRequestNotification($business));

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Solicitação enviada! Em breve um admin irá analisá-la.');
    }

    public function approveUpgrade(Business $business): RedirectResponse
    {
        $business->update([
            'plan' => BusinessPlan::Featured,
            'plan_upgrade_requested_at' => null,
        ]);

        Cache::forget('home:featured_businesses');

        if ($business->user) {
            $business->user->notify(new PlanUpgradeApprovedNotification($business));
        }

        return redirect()->route('admin.moderation.index')
            ->with('success', "Upgrade de \"{$business->name}\" aprovado.");
    }

    public function dismissUpgrade(Business $business): RedirectResponse
    {
        $business->update(['plan_upgrade_requested_at' => null]);

        return redirect()->route('admin.moderation.index')
            ->with('success', "Solicitação de upgrade de \"{$business->name}\" dispensada.");
    }
}

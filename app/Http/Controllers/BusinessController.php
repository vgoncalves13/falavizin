<?php

namespace App\Http\Controllers;

use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Enums\BusinessPlan;
use App\Enums\BusinessStatus;
use App\Http\Requests\MapBusinessesRequest;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\BusinessAnalytics;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PlanUpgradeApprovedNotification;
use App\Notifications\PlanUpgradeRequestNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(): View
    {
        $mapCenter = [
            'lat' => (float) Setting::get('neighborhood_lat'),
            'lng' => (float) Setting::get('neighborhood_lng'),
        ];

        return view('businesses.index', compact('mapCenter'));
    }

    public function map(MapBusinessesRequest $request): JsonResponse
    {
        $bounds = $request->validated();
        $businesses = Business::query()
            ->where('status', BusinessStatus::Approved)
            ->whereBetween('lat', [$bounds['south'], $bounds['north']])
            ->whereBetween('lng', [$bounds['west'], $bounds['east']])
            ->with('category:id,name')
            ->orderByRaw("plan = 'featured' DESC")
            ->orderBy('name')
            ->limit(201)
            ->get();

        $truncated = $businesses->count() > 200;

        return response()->json([
            'data' => $businesses->take(200)->map(fn (Business $business) => [
                'id' => $business->id,
                'name' => $business->name,
                'category' => $business->category?->name,
                'neighborhood' => $business->neighborhood,
                'lat' => (float) $business->lat,
                'lng' => (float) $business->lng,
                'url' => route('businesses.show', $business),
                'featured' => $business->plan === BusinessPlan::Featured,
            ])->values(),
            'truncated' => $truncated,
        ]);
    }

    public function show(Business $business): View
    {
        Gate::authorize('view', $business);

        $canManage = auth()->user()?->can('update', $business);

        if (! $canManage) {
            BusinessAnalytics::record($business, 'view');
        }

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

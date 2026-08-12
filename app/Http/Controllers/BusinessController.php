<?php

namespace App\Http\Controllers;

use App\Actions\CompleteBusinessInitialAction;
use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Enums\BusinessPlan;
use App\Enums\BusinessStatus;
use App\Http\Requests\MapBusinessesRequest;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\BusinessAnalytics;
use App\Models\Neighborhood;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PlanUpgradeApprovedNotification;
use App\Notifications\PlanUpgradeRequestNotification;
use App\Services\BusinessQrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BusinessController extends Controller
{
    public function index(): View
    {
        $neighborhood = request()->route('neighborhood');

        $mapCenter = [
            'lat' => (float) ($neighborhood->latitude ?? Setting::get('neighborhood_lat')),
            'lng' => (float) ($neighborhood->longitude ?? Setting::get('neighborhood_lng')),
        ];

        return view('businesses.index', compact('neighborhood', 'mapCenter'));
    }

    public function map(MapBusinessesRequest $request): JsonResponse
    {
        $neighborhood = request()->route('neighborhood');
        $bounds = $request->validated();

        $businesses = Business::query()
            ->when($neighborhood, fn ($q) => $q->forNeighborhood($neighborhood))
            ->where('status', BusinessStatus::Approved)
            ->whereBetween('lat', [$bounds['south'], $bounds['north']])
            ->whereBetween('lng', [$bounds['west'], $bounds['east']])
            ->with('category:id,name')
            ->with('localNeighborhood:id,name,state_code,city_slug,slug')
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
                'neighborhood' => $business->localNeighborhood->name,
                'lat' => (float) $business->lat,
                'lng' => (float) $business->lng,
                'url' => $business->canonicalUrl(),
                'featured' => $business->plan === BusinessPlan::Featured,
            ])->values(),
            'truncated' => $truncated,
        ]);
    }

    public function show(): View
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('business');

        $business = Business::query()
            ->where('slug', $slug)
            ->when($neighborhood, fn ($q) => $q->forNeighborhood($neighborhood))
            ->firstOrFail();

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
        $neighborhood = request()->route('neighborhood');

        return view('businesses.create', compact('neighborhood'));
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $neighborhood = request()->route('neighborhood');

        $business = (new CreateBusinessAction)->execute(
            user: auth()->user(),
            neighborhood: $neighborhood,
            data: $request->validated(),
            coverPhoto: $request->file('cover_photo'),
        );

        return redirect()->route('neighborhood.businesses.index', $neighborhood->routeParameters())
            ->with('success', 'Negócio enviado! Aguarda aprovação do admin.');
    }

    public function edit(): View
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('business');

        $business = Business::query()
            ->where('slug', $slug)
            ->when($neighborhood, fn ($q) => $q->forNeighborhood($neighborhood))
            ->firstOrFail();

        Gate::authorize('update', $business);

        $business->load(['category', 'coverPhoto']);

        $neighborhood = request()->route('neighborhood') ?? $business->localNeighborhood;

        return view('businesses.edit', compact('business', 'neighborhood'));
    }

    public function update(UpdateBusinessRequest $request): RedirectResponse
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('business');

        $business = Business::query()
            ->where('slug', $slug)
            ->when($neighborhood, fn ($q) => $q->forNeighborhood($neighborhood))
            ->firstOrFail();

        (new UpdateBusinessAction)->execute(
            business: $business,
            data: $request->validated(),
            coverPhoto: $request->file('cover_photo'),
        );

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Negócio atualizado com sucesso!');
    }

    public function onboarding(): View
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('business');

        $business = Business::query()
            ->where('slug', $slug)
            ->when($neighborhood, fn ($q) => $q->forNeighborhood($neighborhood))
            ->firstOrFail();

        Gate::authorize('update', $business);

        $business->load(['category', 'coverPhoto', 'managers']);

        $neighborhood = request()->route('neighborhood') ?? $business->localNeighborhood;

        return view('businesses.onboarding', compact('business', 'neighborhood'));
    }

    public function qr(Business $business): View
    {
        Gate::authorize('update', $business);

        return view('businesses.qr', compact('business'));
    }

    public function downloadQr(Business $business, BusinessQrCodeService $qr): Response
    {
        Gate::authorize('update', $business);

        $filename = Str::slug($business->name).'-qr.png';

        return response($qr->pngFor($business))
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function confirmQr(Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        (new CompleteBusinessInitialAction)->execute(
            $business,
            auth()->user(),
            'qr',
            $business->canonicalUrl(),
        );

        return redirect()->route('businesses.onboarding', $business)
            ->with('success', 'QR Code confirmado! A etapa de ação inicial foi concluída.');
    }

    public function trackShare(Business $business): JsonResponse
    {
        if (auth()->user()?->can('update', $business)) {
            (new CompleteBusinessInitialAction)->execute(
                $business,
                auth()->user(),
                'share',
                $business->canonicalUrl(),
            );
        }

        return response()->json(['ok' => true]);
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

    public function destroy(): RedirectResponse
    {
        /** @var Neighborhood $neighborhood */
        $neighborhood = request()->route('neighborhood');
        $business = Business::query()
            ->where('slug', request()->route('business'))
            ->forNeighborhood($neighborhood)
            ->firstOrFail();

        Gate::authorize('delete', $business);

        $name = $business->name;

        $business->delete();

        return redirect()->route('neighborhood.businesses.index', $neighborhood->routeParameters())
            ->with('success', "Negócio \"{$name}\" removido com sucesso.");
    }
}

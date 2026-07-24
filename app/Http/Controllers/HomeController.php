<?php

namespace App\Http\Controllers;

use App\Enums\PostResolutionStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\User;
use App\Services\HomeCache;
use App\Services\NeighborhoodCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $neighborhoodName = Setting::get('neighborhood_name', 'nosso bairro');

        $categories = HomeCache::remember(HomeCache::CATEGORIES, fn () => Category::query()
            ->orderBy('sort_order')
            ->get()
        );

        $featuredBusinesses = HomeCache::remember(HomeCache::FEATURED_BUSINESSES, fn () => Business::query()
            ->featured()
            ->with(['category', 'coverPhoto'])
            ->limit(4)
            ->get()
        );

        $recentPromotions = HomeCache::remember(HomeCache::PROMOTIONS, fn () => Promotion::query()
            ->active()
            ->with('business.category')
            ->latest()
            ->limit(4)
            ->get()
        );

        $upcomingEvents = HomeCache::remember(HomeCache::UPCOMING_EVENTS, fn () => Post::query()
            ->upcomingEvents()
            ->with(['user', 'category', 'serviceCategory'])
            ->orderBy('event_starts_at')
            ->limit(3)
            ->get()
        );

        $sponsoredPosts = HomeCache::remember(HomeCache::SPONSORED_POSTS, fn () => Post::query()
            ->approved()
            ->sponsored()
            ->with(['user', 'category', 'serviceCategory'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(3)
            ->get()
        );

        $recentPosts = HomeCache::remember(HomeCache::POSTS, fn () => Post::query()
            ->approved()
            ->where('is_sponsored', false)
            ->with(['user', 'category', 'serviceCategory'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(5)
            ->get()
        );

        $recentRequests = HomeCache::remember(HomeCache::REQUESTS, fn () => Post::query()
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'pedido'))
            ->with(['user', 'category', 'serviceCategory'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(3)
            ->get()
        );

        $weekStart = now()->startOfWeek();

        $pulsoPostsThisWeek = HomeCache::remember(HomeCache::PULSE_POSTS, fn () => Post::query()
            ->approved()
            ->where('created_at', '>=', $weekStart)
            ->count()
        );

        $pulsoResolvedThisWeek = HomeCache::remember(HomeCache::PULSE_RESOLVED, fn () => Post::query()
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where('resolution_status', PostResolutionStatus::Resolvido->value)
            ->where('resolved_at', '>=', $weekStart)
            ->count()
        );

        $heroStats = HomeCache::remember(HomeCache::STATS, fn () => [
            'users' => User::count(),
            'businesses' => Business::where('status', 'approved')->count(),
            'posts' => Post::approved()->count(),
        ]);

        return view('home.index', compact(
            'neighborhoodName',
            'categories',
            'featuredBusinesses',
            'recentPromotions',
            'upcomingEvents',
            'sponsoredPosts',
            'recentPosts',
            'recentRequests',
            'pulsoPostsThisWeek',
            'pulsoResolvedThisWeek',
            'heroStats',
        ));
    }

    public function local(NeighborhoodCache $cache): View
    {
        /** @var Neighborhood $neighborhood */
        $neighborhood = request()->route('neighborhood');

        $neighborhoodName = $neighborhood->name;

        $categories = $cache->remember($neighborhood, NeighborhoodCache::HOME_CATEGORIES, fn () => Category::query()
            ->orderBy('sort_order')
            ->get()
        );

        $featuredBusinesses = $cache->remember($neighborhood, NeighborhoodCache::HOME_BUSINESSES, fn () => Business::query()
            ->forNeighborhood($neighborhood)
            ->featured()
            ->with(['category', 'coverPhoto'])
            ->limit(4)
            ->get()
        );

        $recentPromotions = $cache->remember($neighborhood, NeighborhoodCache::HOME_PROMOTIONS, fn () => Promotion::query()
            ->whereHas('business', fn ($q) => $q->where('neighborhood_id', $neighborhood->id))
            ->active()
            ->with('business.category')
            ->latest()
            ->limit(4)
            ->get()
        );

        $upcomingEvents = $cache->remember($neighborhood, NeighborhoodCache::HOME_EVENTS, fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->upcomingEvents()
            ->with(['user', 'category', 'serviceCategory'])
            ->orderBy('event_starts_at')
            ->limit(3)
            ->get()
        );

        $sponsoredPosts = $cache->remember($neighborhood, NeighborhoodCache::HOME_SPONSORED, fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->sponsored()
            ->with(['user', 'category', 'serviceCategory'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(3)
            ->get()
        );

        $recentPosts = $cache->remember($neighborhood, NeighborhoodCache::HOME_POSTS, fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->where('is_sponsored', false)
            ->with(['user', 'category', 'serviceCategory'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(5)
            ->get()
        );

        $recentRequests = $cache->remember($neighborhood, NeighborhoodCache::HOME_REQUESTS, fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'pedido'))
            ->with(['user', 'category', 'serviceCategory'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(3)
            ->get()
        );

        $weekStart = now()->startOfWeek();

        $pulsoPostsThisWeek = $cache->remember($neighborhood, NeighborhoodCache::HOME_PULSE_POSTS, fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->where('created_at', '>=', $weekStart)
            ->count()
        );

        $pulsoResolvedThisWeek = $cache->remember($neighborhood, NeighborhoodCache::HOME_PULSE_RESOLVED, fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where('resolution_status', PostResolutionStatus::Resolvido->value)
            ->where('resolved_at', '>=', $weekStart)
            ->count()
        );

        $heroStats = $cache->remember($neighborhood, NeighborhoodCache::HOME_STATS, fn () => [
            'users' => $neighborhood->users()->count(),
            'businesses' => Business::where('neighborhood_id', $neighborhood->id)->where('status', 'approved')->count(),
            'posts' => Post::forNeighborhood($neighborhood)->approved()->count(),
        ]);

        return view('home.index', compact(
            'neighborhood',
            'neighborhoodName',
            'categories',
            'featuredBusinesses',
            'recentPromotions',
            'upcomingEvents',
            'sponsoredPosts',
            'recentPosts',
            'recentRequests',
            'pulsoPostsThisWeek',
            'pulsoResolvedThisWeek',
            'heroStats',
        ));
    }
}

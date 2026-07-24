<?php

namespace App\Http\Controllers;

use App\Enums\BusinessStatus;
use App\Enums\PostResolutionStatus;
use App\Models\Business;
use App\Models\Post;
use App\Services\NeighborhoodCache;
use Illuminate\View\View;

class PulsoController extends Controller
{
    public function index(NeighborhoodCache $cache): View
    {
        $neighborhood = request()->route('neighborhood');
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $postsByCategory = $cache->remember($neighborhood, NeighborhoodCache::PULSE_PREFIX.':posts_by_category', function () use ($neighborhood, $weekStart) {
            return Post::query()
                ->forNeighborhood($neighborhood)
                ->approved()
                ->where('created_at', '>=', $weekStart)
                ->with('category')
                ->get()
                ->groupBy('category.name')
                ->map->count()
                ->sortDesc()
                ->take(6);
        });

        $topProblems = $cache->remember($neighborhood, NeighborhoodCache::PULSE_PREFIX.':top_problems', fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where(fn ($q) => $q
                ->whereNull('resolution_status')
                ->orWhere('resolution_status', '!=', PostResolutionStatus::Resolvido->value)
            )
            ->withCount('votes')
            ->with(['user', 'category', 'serviceCategory'])
            ->orderByDesc('votes_count')
            ->limit(3)
            ->get()
        );

        $resolvedThisWeek = $cache->remember($neighborhood, NeighborhoodCache::PULSE_PREFIX.':resolved_this_week', fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where('resolution_status', PostResolutionStatus::Resolvido->value)
            ->where('resolved_at', '>=', $weekStart)
            ->count()
        );

        $openProblems = $cache->remember($neighborhood, NeighborhoodCache::PULSE_PREFIX.':open_problems', fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where(fn ($q) => $q
                ->whereNull('resolution_status')
                ->orWhere('resolution_status', '!=', PostResolutionStatus::Resolvido->value)
            )
            ->count()
        );

        $totalProblems = $openProblems + Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where('resolution_status', PostResolutionStatus::Resolvido->value)
            ->count();

        $resolutionRate = $totalProblems > 0
            ? round(($totalProblems - $openProblems) / $totalProblems * 100)
            : 0;

        $inProgressCount = Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where('resolution_status', PostResolutionStatus::EmAndamento->value)
            ->count();

        $topBusiness = $cache->remember($neighborhood, NeighborhoodCache::PULSE_PREFIX.':top_business', fn () => Business::query()
            ->forNeighborhood($neighborhood)
            ->where('status', BusinessStatus::Approved->value)
            ->withCount(['reviews as positive_reviews_count' => fn ($q) => $q->where('rating', '>=', 4)])
            ->orderByDesc('positive_reviews_count')
            ->with('category')
            ->limit(3)
            ->get()
        );

        $postsThisWeek = $cache->remember($neighborhood, NeighborhoodCache::PULSE_PREFIX.':posts_this_week', fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->where('created_at', '>=', $weekStart)
            ->count()
        );

        $activeRequests = $cache->remember($neighborhood, NeighborhoodCache::PULSE_PREFIX.':active_requests', fn () => Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'pedido'))
            ->where('created_at', '>=', now()->subDays(7))
            ->withCount('comments')
            ->with(['user', 'category', 'serviceCategory'])
            ->orderByDesc('comments_count')
            ->limit(3)
            ->get()
        );

        $requestsCount = Post::query()
            ->forNeighborhood($neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'pedido'))
            ->where('created_at', '>=', $weekStart)
            ->count();

        $activeBusinesses = Business::query()
            ->forNeighborhood($neighborhood)
            ->where('status', BusinessStatus::Approved->value)
            ->count();

        return view('pulso.index', compact(
            'neighborhood',
            'postsByCategory',
            'topProblems',
            'resolvedThisWeek',
            'openProblems',
            'totalProblems',
            'resolutionRate',
            'inProgressCount',
            'topBusiness',
            'postsThisWeek',
            'activeRequests',
            'requestsCount',
            'activeBusinesses',
            'weekStart',
            'weekEnd',
        ));
    }
}

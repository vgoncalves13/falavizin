<?php

namespace App\Http\Controllers;

use App\Enums\BusinessStatus;
use App\Enums\PostResolutionStatus;
use App\Models\Business;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PulsoController extends Controller
{
    public function index(): View
    {
        $weekStart = now()->startOfWeek();

        $postsByCategory = Cache::remember('pulso:posts_by_category', 300, function () use ($weekStart) {
            return Post::query()
                ->approved()
                ->where('created_at', '>=', $weekStart)
                ->with('category')
                ->get()
                ->groupBy('category.name')
                ->map->count()
                ->sortDesc()
                ->take(6);
        });

        $topProblems = Cache::remember('pulso:top_problems', 300, fn () => Post::query()
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

        $resolvedThisWeek = Cache::remember('pulso:resolved_this_week', 300, fn () => Post::query()
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where('resolution_status', PostResolutionStatus::Resolvido->value)
            ->where('resolved_at', '>=', $weekStart)
            ->count()
        );

        $openProblems = Cache::remember('pulso:open_problems', 300, fn () => Post::query()
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'problema'))
            ->where(fn ($q) => $q
                ->whereNull('resolution_status')
                ->orWhere('resolution_status', '!=', PostResolutionStatus::Resolvido->value)
            )
            ->count()
        );

        $topBusiness = Cache::remember('pulso:top_business', 300, fn () => Business::query()
            ->where('status', BusinessStatus::Approved->value)
            ->withCount(['reviews as positive_reviews_count' => fn ($q) => $q->where('rating', '>=', 4)])
            ->orderByDesc('positive_reviews_count')
            ->with('category')
            ->limit(3)
            ->get()
        );

        $postsThisWeek = Cache::remember('pulso:posts_this_week', 300, fn () => Post::query()
            ->approved()
            ->where('created_at', '>=', $weekStart)
            ->count()
        );

        $activeRequests = Cache::remember('pulso:active_requests', 300, fn () => Post::query()
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'pedido'))
            ->where('created_at', '>=', now()->subDays(7))
            ->withCount('comments')
            ->with(['user', 'category', 'serviceCategory'])
            ->orderByDesc('comments_count')
            ->limit(3)
            ->get()
        );

        return view('pulso.index', compact(
            'postsByCategory',
            'topProblems',
            'resolvedThisWeek',
            'openProblems',
            'topBusiness',
            'postsThisWeek',
            'activeRequests',
        ));
    }
}

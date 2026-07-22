<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessAnalytics;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(): View
    {
        // Totals
        $totals = [
            'users' => User::count(),
            'posts' => Post::where('status', PostStatus::Approved)->count(),
            'businesses' => Business::where('status', BusinessStatus::Approved)->count(),
            'comments' => Comment::where('status', 'approved')->count(),
        ];

        // This week
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $thisWeek = [
            'users' => User::where('created_at', '>=', $weekStart)->count(),
            'posts' => Post::where('created_at', '>=', $weekStart)->count(),
            'businesses' => Business::where('created_at', '>=', $weekStart)->count(),
        ];

        // Weekly trend — last 8 weeks (3 queries, one per model)
        $from = now()->startOfWeek(Carbon::MONDAY)->subWeeks(7);

        $weeklyPosts = Post::selectRaw('YEARWEEK(created_at, 1) as yw, COUNT(*) as total')
            ->where('created_at', '>=', $from)
            ->groupBy('yw')
            ->pluck('total', 'yw');

        $weeklyBusinesses = Business::selectRaw('YEARWEEK(created_at, 1) as yw, COUNT(*) as total')
            ->where('created_at', '>=', $from)
            ->groupBy('yw')
            ->pluck('total', 'yw');

        $weeklyUsers = User::selectRaw('YEARWEEK(created_at, 1) as yw, COUNT(*) as total')
            ->where('created_at', '>=', $from)
            ->groupBy('yw')
            ->pluck('total', 'yw');

        $weeks = collect(range(7, 0))->map(function (int $ago) use ($weeklyPosts, $weeklyBusinesses, $weeklyUsers) {
            $weekStart = now()->startOfWeek(Carbon::MONDAY)->subWeeks($ago);
            $yw = (int) $weekStart->format('oW'); // ISO year + week (matches YEARWEEK mode 1)

            return [
                'label' => $ago === 0 ? 'Esta sem.' : $weekStart->format('d/m'),
                'posts' => (int) ($weeklyPosts[$yw] ?? 0),
                'businesses' => (int) ($weeklyBusinesses[$yw] ?? 0),
                'users' => (int) ($weeklyUsers[$yw] ?? 0),
            ];
        });

        // Pending / reported (reuse moderation count)
        $pending = [
            'posts' => Post::where('status', PostStatus::Pending)->count(),
            'businesses' => Business::where('status', BusinessStatus::Pending)->count(),
            'promotions' => Promotion::where('status', 'pending')->count(),
        ];

        // Business analytics (last 30 days)
        $analyticsStartDate = now()->subDays(30)->toDateString();
        $businessAnalytics = BusinessAnalytics::query()
            ->where('recorded_date', '>=', $analyticsStartDate)
            ->selectRaw('event_type, SUM(count) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $topViewedBusinesses = Business::query()
            ->where('status', BusinessStatus::Approved)
            ->select(['id', 'name', 'slug'])
            ->selectSub(
                BusinessAnalytics::query()
                    ->where('event_type', 'view')
                    ->where('recorded_date', '>=', $analyticsStartDate)
                    ->whereColumn('business_id', 'businesses.id')
                    ->selectRaw('SUM(count)'),
                'views_count'
            )
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        $topContactedBusinesses = Business::query()
            ->where('status', BusinessStatus::Approved)
            ->select(['id', 'name', 'slug'])
            ->selectSub(
                BusinessAnalytics::query()
                    ->whereIn('event_type', ['phone_click', 'whatsapp_click'])
                    ->where('recorded_date', '>=', $analyticsStartDate)
                    ->whereColumn('business_id', 'businesses.id')
                    ->selectRaw('SUM(count)'),
                'contacts_count'
            )
            ->orderByDesc('contacts_count')
            ->limit(5)
            ->get();

        return view('admin.stats', compact(
            'totals',
            'thisWeek',
            'weeks',
            'pending',
            'businessAnalytics',
            'topViewedBusinesses',
            'topContactedBusinesses',
        ));
    }
}

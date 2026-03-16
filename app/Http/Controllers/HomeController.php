<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Cache::remember('home:categories', 300, fn () => Category::query()
            ->orderBy('sort_order')
            ->get()
        );

        $featuredBusinesses = Cache::remember('home:featured_businesses', 300, fn () => Business::query()
            ->featured()
            ->with(['category', 'coverPhoto'])
            ->limit(4)
            ->get()
        );

        $recentPromotions = Cache::remember('home:promotions', 300, fn () => Promotion::query()
            ->active()
            ->with('business.category')
            ->latest()
            ->limit(4)
            ->get()
        );

        $recentPosts = Cache::remember('home:posts', 300, fn () => Post::query()
            ->approved()
            ->with(['user', 'category'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(5)
            ->get()
        );

        return view('home.index', compact(
            'categories',
            'featuredBusinesses',
            'recentPromotions',
            'recentPosts',
        ));
    }
}

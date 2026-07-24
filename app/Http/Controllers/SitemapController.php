<?php

namespace App\Http\Controllers;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $neighborhoods = Neighborhood::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $posts = Post::query()
            ->where('status', PostStatus::Approved)
            ->whereIn('neighborhood_id', $neighborhoods->pluck('id'))
            ->with('neighborhood')
            ->latest('updated_at')
            ->get();

        $businesses = Business::query()
            ->where('status', BusinessStatus::Approved)
            ->whereIn('neighborhood_id', $neighborhoods->pluck('id'))
            ->with('localNeighborhood')
            ->latest('updated_at')
            ->get();

        $categories = Category::query()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', compact('neighborhoods', 'posts', 'businesses', 'categories'))
            ->header('Content-Type', 'application/xml');
    }
}

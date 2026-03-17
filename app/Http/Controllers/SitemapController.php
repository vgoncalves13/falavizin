<?php

namespace App\Http\Controllers;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::query()
            ->where('status', PostStatus::Approved)
            ->latest('updated_at')
            ->get(['slug', 'updated_at']);

        $businesses = Business::query()
            ->where('status', BusinessStatus::Approved)
            ->latest('updated_at')
            ->get(['slug', 'updated_at']);

        $categories = Category::query()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', compact('posts', 'businesses', 'categories'))
            ->header('Content-Type', 'application/xml');
    }
}

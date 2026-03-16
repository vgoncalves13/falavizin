<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Models\Business;
use App\Models\Category;
use App\Models\Post;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $posts = match ($category->type) {
            CategoryType::Post, CategoryType::Both => Post::query()
                ->approved()
                ->where('category_id', $category->id)
                ->with(['user', 'category'])
                ->withCount(['comments', 'votes'])
                ->latest()
                ->paginate(10),
            default => null,
        };

        $businesses = match ($category->type) {
            CategoryType::Business, CategoryType::Both => Business::query()
                ->where('status', 'approved')
                ->where('category_id', $category->id)
                ->with(['category', 'coverPhoto'])
                ->orderByRaw("plan = 'featured' DESC")
                ->orderBy('name')
                ->paginate(12),
            default => null,
        };

        return view('categories.show', compact('category', 'posts', 'businesses'));
    }
}

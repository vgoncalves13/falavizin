<?php

namespace App\Http\Controllers;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim();

        $posts = collect();
        $businesses = collect();

        if ($query->isNotEmpty()) {
            $posts = Post::query()
                ->approved()
                ->where(fn ($q) => $q
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%")
                )
                ->with(['user', 'category', 'serviceCategory'])
                ->latest()
                ->limit(15)
                ->get();

            $businesses = Business::query()
                ->where('status', BusinessStatus::Approved)
                ->where(fn ($q) => $q
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('neighborhood', 'like', "%{$query}%")
                )
                ->with(['category', 'coverPhoto'])
                ->latest()
                ->limit(15)
                ->get();
        }

        return view('search.index', compact('query', 'posts', 'businesses'));
    }
}

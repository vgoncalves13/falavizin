<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SponsoredPostsController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $posts = Post::query()
            ->where('status', PostStatus::Approved)
            ->with(['user', 'category'])
            ->withCount('comments')
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")))
            ->orderByDesc('is_sponsored')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $sponsoredCount = Post::where('status', PostStatus::Approved)
            ->where('is_sponsored', true)
            ->count();

        return view('admin.sponsored-posts', compact('posts', 'sponsoredCount', 'search'));
    }

    public function toggle(Post $post): RedirectResponse
    {
        $post->update(['is_sponsored' => ! $post->is_sponsored]);

        Cache::forget('home:posts');
        Cache::forget('home:sponsored_posts');

        $status = $post->is_sponsored ? 'patrocinado' : 'removido dos patrocinados';

        return back()->with('success', "Post \"{$post->title}\" {$status}.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ToggleSponsorAction;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $sponsoredCount = Post::query()->sponsored()->count();

        return view('admin.sponsored-posts', compact('posts', 'sponsoredCount', 'search'));
    }

    public function toggle(Post $post, ToggleSponsorAction $action, Request $request): RedirectResponse
    {
        $days = $request->input('days') ? (int) $request->input('days') : null;

        $action->execute($post, $days);

        $status = $post->fresh()->is_sponsored ? 'patrocinado' : 'removido dos patrocinados';

        return back()->with('success', "Post \"{$post->title}\" {$status}.");
    }
}

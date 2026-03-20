<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('feed.index');
    }

    public function show(Post $post): View
    {
        $post->load(['user', 'category', 'votes', 'poll.options', 'poll.votes']);

        $relatedPosts = Post::query()
            ->approved()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->with(['user', 'category'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(3)
            ->get();

        return view('feed.show', compact('post', 'relatedPosts'));
    }

    public function create(): View
    {
        return view('feed.create');
    }

    public function edit(Post $post): View
    {
        return view('feed.edit', compact('post'));
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('feed.index')
            ->with('success', 'Post removido.');
    }
}

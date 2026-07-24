<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        /** @var Neighborhood $neighborhood */
        $neighborhood = request()->route('neighborhood');

        return view('feed.index', compact('neighborhood'));
    }

    public function show(): View
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('post');

        $query = Post::query()->where('slug', $slug);
        if ($neighborhood) {
            $query->where('neighborhood_id', $neighborhood->id);
        }
        $post = $query->firstOrFail();

        Gate::authorize('view', $post);

        $post->load(['user', 'category', 'serviceCategory', 'votes', 'poll.options', 'poll.votes', 'interests.businesses']);

        $relatedPosts = Post::query()
            ->approved()
            ->forNeighborhood($post->neighborhood_id)
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->with(['user', 'category', 'serviceCategory'])
            ->withCount(['comments', 'votes'])
            ->latest()
            ->limit(3)
            ->get();

        return view('feed.show', compact('post', 'relatedPosts'));
    }

    public function create(): View
    {
        $neighborhood = request()->route('neighborhood') ?? request()->user()->primaryNeighborhood;

        return view('feed.create', compact('neighborhood'));
    }

    public function edit(): View
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('post');

        $query = Post::query()->where('slug', $slug);
        if ($neighborhood) {
            $query->where('neighborhood_id', $neighborhood->id);
        }
        $post = $query->firstOrFail();

        return view('feed.edit', compact('post'));
    }

    public function destroy(): RedirectResponse
    {
        $neighborhood = request()->route('neighborhood');
        $slug = request()->route('post');

        $query = Post::query()->where('slug', $slug);
        if ($neighborhood) {
            $query->where('neighborhood_id', $neighborhood->id);
        }
        $post = $query->firstOrFail();

        Gate::authorize('delete', $post);

        $post->delete();

        $redirectRoute = $neighborhood
            ? route('neighborhood.feed.index', $neighborhood->routeParameters())
            : route('feed.index');

        return redirect($redirectRoute)->with('success', 'Post removido.');
    }
}

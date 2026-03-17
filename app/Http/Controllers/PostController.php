<?php

namespace App\Http\Controllers;

use App\Actions\CreatePostAction;
use App\Http\Requests\StorePostRequest;
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
        $post->load(['user', 'category', 'votes']);

        return view('feed.show', compact('post'));
    }

    public function create(): View
    {
        return view('feed.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = (new CreatePostAction)->execute(
            user: auth()->user(),
            data: $request->validated(),
        );

        return redirect()->route('feed.index')
            ->with('success', 'Post enviado! Aguarda aprovação do admin.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('feed.index')
            ->with('success', 'Post removido.');
    }
}

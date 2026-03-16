<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('feed.index');
    }

    public function show(Post $post): View
    {
        return view('feed.show', compact('post'));
    }

    public function create(): View
    {
        return view('feed.create');
    }

    public function store(): RedirectResponse
    {
        return redirect()->route('feed.index');
    }

    public function destroy(Post $post): RedirectResponse
    {
        return redirect()->route('feed.index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function account(Request $request): View
    {
        $user = $request->user()->loadCount([
            'posts',
            'businesses',
            'comments',
            'favorites',
            'savedPosts',
        ]);

        $posts = $user->posts()->with('category')->latest()
            ->paginate(10, ['*'], 'posts_page')->appends(['tab' => 'posts']);
        $businesses = $user->businesses()->with('category')->latest()
            ->paginate(10, ['*'], 'businesses_page')->appends(['tab' => 'businesses']);
        $comments = $user->comments()->with('post')->latest()
            ->paginate(10, ['*'], 'comments_page')->appends(['tab' => 'comments']);
        $favorites = $user->favorites()->with(['category', 'coverPhoto'])
            ->paginate(10, ['*'], 'favorites_page')->appends(['tab' => 'favorites']);
        $savedPosts = $user->savedPosts()->with(['category', 'user'])
            ->paginate(10, ['*'], 'saved_page')->appends(['tab' => 'saved']);
        $requestedTab = $request->string('tab')->value();
        $activeTab = in_array($requestedTab, ['posts', 'businesses', 'comments', 'favorites', 'saved'], true)
            ? $requestedTab
            : 'posts';

        return view('profile.account', compact(
            'user',
            'posts',
            'businesses',
            'comments',
            'favorites',
            'savedPosts',
            'activeTab',
        ));
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $user->businesses()->update([
            'user_id' => null,
            'claimed' => false,
            'claimed_at' => null,
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

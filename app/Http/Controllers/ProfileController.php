<?php

namespace App\Http\Controllers;

use App\Actions\UpdateProfileAction;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Business;
use App\Models\BusinessManager;
use App\Models\Neighborhood;
use App\Models\Post;
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

        $managedIds = $user->managedBusinesses()->pluck('businesses.id');

        $businessesQuery = Business::query()
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhereIn('id', $managedIds));

        $posts = $user->posts()->with(['category', 'serviceCategory'])->latest()
            ->paginate(10, ['*'], 'posts_page')->appends(['tab' => 'posts']);
        $businesses = (clone $businessesQuery)->with(['category', 'categories'])->latest()
            ->paginate(10, ['*'], 'businesses_page')->appends(['tab' => 'businesses']);
        $comments = $user->comments()->with('post')->latest()
            ->paginate(10, ['*'], 'comments_page')->appends(['tab' => 'comments']);
        $favorites = $user->favorites()->with(['category', 'coverPhoto'])
            ->paginate(10, ['*'], 'favorites_page')->appends(['tab' => 'favorites']);
        $savedPosts = $user->savedPosts()->with(['category', 'user', 'serviceCategory'])
            ->paginate(10, ['*'], 'saved_page')->appends(['tab' => 'saved']);

        $businessCategoryIds = (clone $businessesQuery)
            ->with('categories')
            ->get()
            ->flatMap(fn ($business) => $business->categories->pluck('id'))
            ->unique()
            ->filter()
            ->values();

        $businessNeighborhoodIds = (clone $businessesQuery)
            ->pluck('neighborhood_id')
            ->unique()
            ->filter()
            ->values();

        $relevantRequests = collect();
        if ($businessCategoryIds->isNotEmpty() && $businessNeighborhoodIds->isNotEmpty()) {
            $relevantRequests = Post::query()
                ->approved()
                ->whereHas('category', fn ($q) => $q->where('slug', 'pedido'))
                ->whereIn('service_category_id', $businessCategoryIds)
                ->whereIn('neighborhood_id', $businessNeighborhoodIds)
                ->with(['user', 'category', 'serviceCategory'])
                ->withCount(['comments', 'votes'])
                ->latest()
                ->paginate(10, ['*'], 'requests_page')
                ->appends(['tab' => 'requests']);
        }

        $requestedTab = $request->string('tab')->value();
        $activeTab = in_array($requestedTab, ['posts', 'businesses', 'comments', 'favorites', 'saved', 'requests', 'notifications'], true)
            ? $requestedTab
            : 'posts';

        return view('profile.account', compact(
            'user',
            'posts',
            'businesses',
            'comments',
            'favorites',
            'savedPosts',
            'relevantRequests',
            'businessCategoryIds',
            'activeTab',
        ));
    }

    public function edit(Request $request): View
    {
        $neighborhoods = Neighborhood::query()->active()->orderBy('sort_order')->orderBy('name')->get();

        return view('profile.edit', [
            'user' => $request->user(),
            'neighborhoods' => $neighborhoods,
        ]);
    }

    public function update(ProfileUpdateRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

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

        BusinessManager::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

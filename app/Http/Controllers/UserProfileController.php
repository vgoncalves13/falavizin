<?php

namespace App\Http\Controllers;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\User;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(User $user): View
    {
        $posts = $user->posts()
            ->with(['category', 'user'])
            ->withCount(['comments', 'votes'])
            ->where('status', PostStatus::Approved)
            ->latest()
            ->get();

        $businesses = $user->businesses()
            ->with(['category', 'coverPhoto'])
            ->where('status', BusinessStatus::Approved)
            ->latest()
            ->get();

        return view('users.show', compact('user', 'posts', 'businesses'));
    }
}

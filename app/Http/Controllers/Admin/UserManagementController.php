<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $roleFilter = $request->string('role')->value();

        $users = User::query()
            ->when($search, fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            )
            ->when($roleFilter, fn ($q) => $q->where('role', $roleFilter))
            ->withCount(['posts', 'businesses', 'comments'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', UserRole::Admin)->count(),
            'moderators' => User::where('role', UserRole::Moderator)->count(),
            'users' => User::where('role', UserRole::User)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'search', 'roleFilter'));
    }

    public function updateRole(User $user, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,moderator,admin'],
        ]);

        $user->update(['role' => $validated['role']]);

        $roleLabel = UserRole::from($validated['role'])->label();

        return back()->with('success', "Role de {$user->name} atualizada para {$roleLabel}.");
    }
}

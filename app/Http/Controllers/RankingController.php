<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('points', '>', 0)
            ->orderByDesc('points')
            ->limit(50)
            ->get(['id', 'name', 'neighborhood', 'points', 'created_at']);

        return view('users.ranking', compact('users'));
    }
}

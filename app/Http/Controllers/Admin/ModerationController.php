<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(): View
    {
        return view('admin.moderation.index');
    }

    public function approve(string $type, int $id): RedirectResponse
    {
        return redirect()->route('admin.moderation.index');
    }

    public function reject(string $type, int $id): RedirectResponse
    {
        return redirect()->route('admin.moderation.index');
    }
}

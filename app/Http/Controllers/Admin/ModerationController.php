<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(): View
    {
        $pendingPosts = Post::query()
            ->where('status', PostStatus::Pending)
            ->with(['user', 'category'])
            ->latest()
            ->get();

        $pendingBusinesses = Business::query()
            ->where('status', BusinessStatus::Pending)
            ->with(['user', 'category'])
            ->latest()
            ->get();

        $pendingPromotions = Promotion::query()
            ->where('status', 'pending')
            ->with('business')
            ->latest()
            ->get();

        return view('admin.moderation.index', compact(
            'pendingPosts',
            'pendingBusinesses',
            'pendingPromotions',
        ));
    }

    public function approve(string $type, int $id): RedirectResponse
    {
        match ($type) {
            'post' => Post::findOrFail($id)->update([
                'status' => PostStatus::Approved,
                'approved_at' => now(),
            ]),
            'business' => Business::findOrFail($id)->update([
                'status' => BusinessStatus::Approved,
            ]),
            'promotion' => Promotion::findOrFail($id)->update([
                'status' => 'approved',
            ]),
            default => abort(404),
        };

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Conteúdo aprovado.');
    }

    public function reject(string $type, int $id): RedirectResponse
    {
        match ($type) {
            'post' => Post::findOrFail($id)->update([
                'status' => PostStatus::Rejected,
            ]),
            'business' => Business::findOrFail($id)->update([
                'status' => BusinessStatus::Rejected,
            ]),
            'promotion' => Promotion::findOrFail($id)->update([
                'status' => 'rejected',
            ]),
            default => abort(404),
        };

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Conteúdo rejeitado.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
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

        $reportedPosts = Post::query()
            ->whereNotNull('reported_at')
            ->where('status', PostStatus::Approved)
            ->with(['user', 'category'])
            ->latest('reported_at')
            ->get();

        $reportedBusinesses = Business::query()
            ->whereNotNull('reported_at')
            ->where('status', BusinessStatus::Approved)
            ->with(['user', 'category'])
            ->latest('reported_at')
            ->get();

        $reportedPromotions = Promotion::query()
            ->whereNotNull('reported_at')
            ->where('status', 'approved')
            ->with('business')
            ->latest('reported_at')
            ->get();

        return view('admin.moderation.index', compact(
            'pendingPosts',
            'pendingBusinesses',
            'pendingPromotions',
            'reportedPosts',
            'reportedBusinesses',
            'reportedPromotions',
        ));
    }

    public function approve(string $type, int $id): RedirectResponse
    {
        match ($type) {
            'post' => Post::findOrFail($id)->update([
                'status' => PostStatus::Approved,
                'approved_at' => now(),
                'reported_at' => null,
            ]),
            'business' => Business::findOrFail($id)->update([
                'status' => BusinessStatus::Approved,
                'reported_at' => null,
            ]),
            'promotion' => Promotion::findOrFail($id)->update([
                'status' => 'approved',
                'reported_at' => null,
            ]),
            default => abort(404),
        };

        $this->clearHomeCache();

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Conteúdo aprovado.');
    }

    public function reject(string $type, int $id): RedirectResponse
    {
        match ($type) {
            'post' => Post::findOrFail($id)->update([
                'status' => PostStatus::Rejected,
                'reported_at' => null,
            ]),
            'business' => Business::findOrFail($id)->update([
                'status' => BusinessStatus::Rejected,
                'reported_at' => null,
            ]),
            'promotion' => Promotion::findOrFail($id)->update([
                'status' => 'rejected',
                'reported_at' => null,
            ]),
            default => abort(404),
        };

        $this->clearHomeCache();

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Conteúdo rejeitado.');
    }

    private function clearHomeCache(): void
    {
        Cache::forget('home:posts');
        Cache::forget('home:featured_businesses');
        Cache::forget('home:promotions');
        Cache::forget('home:categories');
    }
}

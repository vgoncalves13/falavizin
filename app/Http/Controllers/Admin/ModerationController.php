<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ClaimBusinessAction;
use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Post;
use App\Models\Promotion;
use App\Notifications\ContentModerationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->paginate(15, ['*'], 'pending_posts');

        $pendingBusinesses = Business::query()
            ->where('status', BusinessStatus::Pending)
            ->with(['user', 'category'])
            ->latest()
            ->paginate(15, ['*'], 'pending_businesses');

        $pendingPromotions = Promotion::query()
            ->where('status', 'pending')
            ->with('business')
            ->latest()
            ->paginate(15, ['*'], 'pending_promotions');

        $reportedPosts = Post::query()
            ->whereNotNull('reported_at')
            ->where('status', PostStatus::Approved)
            ->with(['user', 'category'])
            ->latest('reported_at')
            ->paginate(15, ['*'], 'reported_posts');

        $reportedBusinesses = Business::query()
            ->whereNotNull('reported_at')
            ->where('status', BusinessStatus::Approved)
            ->with(['user', 'category'])
            ->latest('reported_at')
            ->paginate(15, ['*'], 'reported_businesses');

        $reportedPromotions = Promotion::query()
            ->whereNotNull('reported_at')
            ->where('status', 'approved')
            ->with('business')
            ->latest('reported_at')
            ->paginate(15, ['*'], 'reported_promotions');

        $pendingUpgrades = Business::query()
            ->whereNotNull('plan_upgrade_requested_at')
            ->with(['user', 'category'])
            ->oldest('plan_upgrade_requested_at')
            ->get();

        $pendingClaims = Business::query()
            ->whereNotNull('claim_user_id')
            ->with(['claimUser', 'category'])
            ->oldest('claim_requested_at')
            ->get();

        return view('admin.moderation.index', compact(
            'pendingPosts',
            'pendingBusinesses',
            'pendingPromotions',
            'reportedPosts',
            'reportedBusinesses',
            'reportedPromotions',
            'pendingUpgrades',
            'pendingClaims',
        ));
    }

    public function approveClaim(Business $business, ClaimBusinessAction $action): RedirectResponse
    {
        $action->execute($business, approved: true);
        $this->clearModerationCache();

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Reivindicação aprovada.');
    }

    public function rejectClaim(Business $business, ClaimBusinessAction $action): RedirectResponse
    {
        $action->execute($business, approved: false);
        $this->clearModerationCache();

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Reivindicação rejeitada.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'type' => ['required', 'in:post,business,promotion'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $action = $validated['action'];
        $type = $validated['type'];
        $ids = $validated['ids'];

        match ($type) {
            'post' => match ($action) {
                'approve' => Post::whereIn('id', $ids)->update(['status' => PostStatus::Approved, 'approved_at' => now(), 'reported_at' => null]),
                'reject' => Post::whereIn('id', $ids)->update(['status' => PostStatus::Rejected, 'reported_at' => null]),
            },
            'business' => match ($action) {
                'approve' => Business::whereIn('id', $ids)->update(['status' => BusinessStatus::Approved, 'reported_at' => null]),
                'reject' => Business::whereIn('id', $ids)->update(['status' => BusinessStatus::Rejected, 'reported_at' => null]),
            },
            'promotion' => match ($action) {
                'approve' => Promotion::whereIn('id', $ids)->update(['status' => 'approved', 'reported_at' => null]),
                'reject' => Promotion::whereIn('id', $ids)->update(['status' => 'rejected', 'reported_at' => null]),
            },
        };

        $this->clearModerationCache();

        $count = count($ids);
        $verb = $action === 'approve' ? 'aprovados' : 'rejeitados';

        return redirect()->route('admin.moderation.index')
            ->with('success', "{$count} item(s) {$verb} com sucesso.");
    }

    public function approve(string $type, int $id): RedirectResponse
    {
        $model = match ($type) {
            'post' => tap(Post::with('user')->findOrFail($id))->update([
                'status' => PostStatus::Approved,
                'approved_at' => now(),
                'reported_at' => null,
            ]),
            'business' => tap(Business::with('user')->findOrFail($id))->update([
                'status' => BusinessStatus::Approved,
                'reported_at' => null,
            ]),
            'promotion' => tap(Promotion::with('business.user')->findOrFail($id))->update([
                'status' => 'approved',
                'reported_at' => null,
            ]),
            default => abort(404),
        };

        $this->notifyAuthor($model, $type, 'approved');
        $this->clearModerationCache();

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Conteúdo aprovado.');
    }

    public function reject(string $type, int $id): RedirectResponse
    {
        $model = match ($type) {
            'post' => tap(Post::with('user')->findOrFail($id))->update([
                'status' => PostStatus::Rejected,
                'reported_at' => null,
            ]),
            'business' => tap(Business::with('user')->findOrFail($id))->update([
                'status' => BusinessStatus::Rejected,
                'reported_at' => null,
            ]),
            'promotion' => tap(Promotion::with('business.user')->findOrFail($id))->update([
                'status' => 'rejected',
                'reported_at' => null,
            ]),
            default => abort(404),
        };

        $this->notifyAuthor($model, $type, 'rejected');
        $this->clearModerationCache();

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Conteúdo rejeitado.');
    }

    private function notifyAuthor(Post|Business|Promotion $model, string $type, string $decision): void
    {
        $author = match ($type) {
            'post' => $model->user,
            'business' => $model->user,
            'promotion' => $model->business?->user,
        };

        if (! $author) {
            return;
        }

        $title = match ($type) {
            'post' => $model->title,
            'business' => $model->name,
            'promotion' => $model->title,
        };

        $url = $decision === 'approved' ? match ($type) {
            'post' => route('feed.show', $model),
            'business' => route('businesses.show', $model),
            'promotion' => route('businesses.show', $model->business),
            default => null,
        } : null;

        $author->notify(new ContentModerationNotification(
            type: $type,
            title: $title,
            decision: $decision,
            url: $url,
        ));
    }

    private function clearModerationCache(): void
    {
        Cache::forget('admin:moderation_count');
    }
}

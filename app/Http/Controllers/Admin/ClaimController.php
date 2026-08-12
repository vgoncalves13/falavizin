<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApproveBusinessClaimAction;
use App\Actions\RejectBusinessClaimAction;
use App\Enums\BusinessClaimStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')
            ->pipe(fn (string $value) => in_array($value, array_column(BusinessClaimStatus::cases(), 'value'), true)
                ? $value
                : BusinessClaimStatus::Pending->value);

        $claims = BusinessClaim::query()
            ->where('status', $status)
            ->with(['business.localNeighborhood', 'user', 'reviewer'])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($query) use ($request) {
                $needle = '%'.$request->string('search')->toString().'%';
                $query->whereHas('business', fn ($q) => $q->where('name', 'like', $needle))
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $needle)->orWhere('email', 'like', $needle));
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => BusinessClaim::where('status', BusinessClaimStatus::Pending)->count(),
            'approved' => BusinessClaim::where('status', BusinessClaimStatus::Approved)->count(),
            'rejected' => BusinessClaim::where('status', BusinessClaimStatus::Rejected)->count(),
        ];

        return view('admin.claims.index', compact('claims', 'status', 'counts'));
    }

    public function approve(BusinessClaim $claim, ApproveBusinessClaimAction $action): RedirectResponse
    {
        Gate::authorize('moderate', $claim);

        $action->execute($claim, auth()->user());
        $action->afterCommit($claim, auth()->user());

        return redirect()->route('admin.claims.index')
            ->with('success', 'Reivindicação aprovada e responsável vinculado.');
    }

    public function reject(Request $request, BusinessClaim $claim, RejectBusinessClaimAction $action): RedirectResponse
    {
        Gate::authorize('moderate', $claim);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $action->execute($claim, auth()->user(), $validated['reason'] ?? null);

        return redirect()->route('admin.claims.index')
            ->with('success', 'Reivindicação rejeitada.');
    }
}

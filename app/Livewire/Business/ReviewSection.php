<?php

namespace App\Livewire\Business;

use App\Models\Business;
use App\Models\Review;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class ReviewSection extends Component
{
    public Business $business;

    public int $rating = 0;

    public string $body = '';

    public ?int $editingId = null;

    public int $editRating = 0;

    public string $editBody = '';

    public ?int $replyingToId = null;

    public string $replyText = '';

    public function mount(): void
    {
        $existing = $this->userReview();

        if ($existing) {
            $this->rating = $existing->rating;
            $this->body = $existing->body ?? '';
        }
    }

    public function saveReview(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = $this->userReview();

        if ($existing) {
            Gate::authorize('update', $existing);
            $existing->update(['rating' => $this->rating, 'body' => $this->body ?: null]);
        } else {
            Review::create([
                'user_id' => auth()->id(),
                'business_id' => $this->business->id,
                'rating' => $this->rating,
                'body' => $this->body ?: null,
            ]);
        }
    }

    public function startReply(int $reviewId): void
    {
        Gate::authorize('update', $this->business);

        $review = $this->business->reviews()->findOrFail($reviewId);
        $this->replyingToId = $reviewId;
        $this->replyText = $review->owner_reply ?? '';
    }

    public function cancelReply(): void
    {
        $this->replyingToId = null;
        $this->replyText = '';
    }

    public function saveReply(): void
    {
        Gate::authorize('update', $this->business);

        $this->validateOnly('replyText', [
            'replyText' => ['required', 'string', 'max:1000'],
        ]);

        $this->business->reviews()->findOrFail($this->replyingToId)->update([
            'owner_reply' => $this->replyText,
            'owner_replied_at' => now(),
        ]);

        $this->replyingToId = null;
        $this->replyText = '';
    }

    public function deleteReply(int $reviewId): void
    {
        Gate::authorize('update', $this->business);

        $this->business->reviews()->findOrFail($reviewId)->update([
            'owner_reply' => null,
            'owner_replied_at' => null,
        ]);
    }

    public function deleteReview(int $reviewId): void
    {
        $review = $this->business->reviews()->findOrFail($reviewId);

        Gate::authorize('delete', $review);

        $review->delete();

        if (auth()->id() === $review->user_id) {
            $this->rating = 0;
            $this->body = '';
        }
    }

    public function render(): View
    {
        $reviews = $this->business->reviews()
            ->with('user')
            ->latest()
            ->get();

        $averageRating = $reviews->avg('rating');
        $averageRating = $averageRating ? round($averageRating, 1) : null;

        $userReview = $this->userReview();

        return view('livewire.business.review-section', compact('reviews', 'averageRating', 'userReview'));
    }

    private function userReview(): ?Review
    {
        if (! auth()->check()) {
            return null;
        }

        return $this->business->reviews()->where('user_id', auth()->id())->first();
    }
}

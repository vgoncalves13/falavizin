<?php

namespace App\Livewire\Feed;

use App\Actions\AwardPointsAction;
use App\Enums\PointEventReason;
use App\Enums\VoteType;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class VoteButtons extends Component
{
    public Post $post;

    public function vote(string $type): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $key = 'vote:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 30)) {
            return;
        }

        RateLimiter::hit($key, 60);

        $voteType = VoteType::from($type);

        $existing = Vote::where('user_id', auth()->id())
            ->where('votable_type', Post::class)
            ->where('votable_id', $this->post->id)
            ->first();

        if ($existing) {
            if ($existing->type === $voteType) {
                $existing->delete();

                return;
            }
            $existing->update(['type' => $voteType]);

            return;
        }

        $vote = Vote::create([
            'user_id' => auth()->id(),
            'votable_type' => Post::class,
            'votable_id' => $this->post->id,
            'type' => $voteType,
        ]);

        if ($voteType === VoteType::Helpful) {
            $postAuthor = $this->post->user;
            if ($postAuthor && $postAuthor->id !== auth()->id()) {
                (new AwardPointsAction)->execute($postAuthor, PointEventReason::VoteReceived, $vote);
            }
        }
    }

    public function render()
    {
        $votes = $this->post->votes()->get();

        $helpfulCount = $votes->where('type', VoteType::Helpful)->count();
        $notHelpfulCount = $votes->where('type', VoteType::NotHelpful)->count();

        $userVote = auth()->check()
            ? $votes->where('user_id', auth()->id())->first()?->type
            : null;

        return view('livewire.feed.vote-buttons', compact('helpfulCount', 'notHelpfulCount', 'userVote'));
    }
}

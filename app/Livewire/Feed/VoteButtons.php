<?php

namespace App\Livewire\Feed;

use App\Enums\VoteType;
use App\Models\Post;
use App\Models\Vote;
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

        Vote::create([
            'user_id' => auth()->id(),
            'votable_type' => Post::class,
            'votable_id' => $this->post->id,
            'type' => $voteType,
        ]);
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

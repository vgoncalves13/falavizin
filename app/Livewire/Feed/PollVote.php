<?php

namespace App\Livewire\Feed;

use App\Actions\AwardPointsAction;
use App\Enums\PointEventReason;
use App\Models\Poll;
use App\Models\PollVote as PollVoteModel;
use Livewire\Component;

class PollVote extends Component
{
    public Poll $poll;

    public ?int $selectedOption = null;

    public function mount(Poll $poll): void
    {
        $this->poll = $poll;

        if (auth()->check()) {
            $vote = PollVoteModel::where('poll_id', $poll->id)
                ->where('user_id', auth()->id())
                ->first();

            $this->selectedOption = $vote?->poll_option_id;
        }
    }

    public function vote(int $optionId): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        if ($this->poll->isEnded()) {
            return;
        }

        $option = $this->poll->options()->findOrFail($optionId);

        $alreadyVoted = PollVoteModel::where('poll_id', $this->poll->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyVoted) {
            return;
        }

        PollVoteModel::create([
            'poll_id' => $this->poll->id,
            'poll_option_id' => $option->id,
            'user_id' => auth()->id(),
        ]);

        $this->selectedOption = $option->id;

        $postAuthor = $this->poll->post?->user;
        if ($postAuthor && $postAuthor->id !== auth()->id()) {
            (new AwardPointsAction)->execute($postAuthor, PointEventReason::VoteReceived, $this->poll->post);
        }

        $this->poll->load('options', 'votes');
    }

    /** @return array<int, array{id: int, text: string, votes: int, percentage: int}> */
    public function percentages(): array
    {
        $this->poll->loadMissing('options', 'votes');
        $total = $this->poll->votes->count();

        return $this->poll->options->map(function ($option) use ($total) {
            $count = $this->poll->votes->where('poll_option_id', $option->id)->count();

            return [
                'id' => $option->id,
                'text' => $option->text,
                'votes' => $count,
                'percentage' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
            ];
        })->toArray();
    }

    public function hasVoted(): bool
    {
        return $this->selectedOption !== null;
    }

    public function render()
    {
        return view('livewire.feed.poll-vote', [
            'percentages' => $this->percentages(),
            'totalVotes' => $this->poll->votes->count(),
            'isEnded' => $this->poll->isEnded(),
        ]);
    }
}

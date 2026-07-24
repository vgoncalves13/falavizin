<?php

namespace App\Livewire\Feed;

use App\Models\Post;
use App\Notifications\InterestNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InterestButton extends Component
{
    public Post $post;

    public bool $isInterested = false;

    public int $interestCount = 0;

    public function mount(): void
    {
        $this->isInterested = $this->post->isInterestedBy(Auth::user());
        $this->interestCount = $this->post->interests()->count();
    }

    public function toggle(): void
    {
        $user = Auth::user();

        Gate::authorize('interact', $this->post);

        if ($this->isInterested) {
            $this->post->interests()->detach($user->id);
            $this->isInterested = false;
            $this->interestCount--;
        } else {
            $this->post->interests()->attach($user->id);
            $this->isInterested = true;
            $this->interestCount++;

            if ($this->post->user_id !== $user->id) {
                $this->post->user->notify(new InterestNotification($this->post, $user));
            }
        }

        $this->dispatch('interestUpdated')->to(InterestList::class);
    }

    public function render()
    {
        return view('livewire.feed.interest-button');
    }
}

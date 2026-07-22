<?php

namespace App\Livewire\Feed;

use App\Models\Post;
use Livewire\Attributes\On;
use Livewire\Component;

class InterestList extends Component
{
    public Post $post;

    #[On('interestUpdated')]
    public function refreshInterests(): void
    {
        $this->post->load('interests.businesses');
    }

    public function render()
    {
        return view('livewire.feed.interest-list');
    }
}

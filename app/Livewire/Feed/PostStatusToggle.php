<?php

namespace App\Livewire\Feed;

use App\Enums\PostResolutionStatus;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PostStatusToggle extends Component
{
    public Post $post;

    public function setStatus(?string $status): void
    {
        Gate::authorize('update', $this->post);

        $resolutionStatus = $status ? PostResolutionStatus::from($status) : null;

        $this->post->update([
            'resolution_status' => $resolutionStatus,
            'resolved_at' => $resolutionStatus === PostResolutionStatus::Resolvido ? now() : null,
        ]);

        $this->post->refresh();
    }

    public function render()
    {
        return view('livewire.feed.post-status-toggle');
    }
}

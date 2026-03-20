<?php

namespace App\Livewire\Feed;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

class SaveButton extends Component
{
    public Post $post;

    public bool $saved = false;

    public int $savesCount = 0;

    public function mount(Post $post): void
    {
        $this->post = $post;
        $this->savesCount = $post->saves()->count();
        $this->saved = auth()->check() && $post->saves()->where('user_id', auth()->id())->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        if ($this->saved) {
            $this->post->saves()->detach(auth()->id());
            $this->saved = false;
            $this->savesCount--;
        } else {
            $this->post->saves()->attach(auth()->id());
            $this->saved = true;
            $this->savesCount++;
        }
    }

    public function render(): View
    {
        return view('livewire.feed.save-button');
    }
}

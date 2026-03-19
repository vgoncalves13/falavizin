<?php

namespace App\Livewire\Business;

use App\Models\Business;
use Livewire\Component;

class FavoriteButton extends Component
{
    public Business $business;

    public bool $isFavorited = false;

    public function mount(): void
    {
        $this->isFavorited = auth()->check()
            && auth()->user()->favorites()->where('business_id', $this->business->id)->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        if ($this->isFavorited) {
            auth()->user()->favorites()->detach($this->business->id);
            $this->isFavorited = false;
        } else {
            auth()->user()->favorites()->attach($this->business->id);
            $this->isFavorited = true;
        }
    }

    public function render()
    {
        return view('livewire.business.favorite-button');
    }
}

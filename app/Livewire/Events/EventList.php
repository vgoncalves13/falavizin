<?php

namespace App\Livewire\Events;

use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EventList extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'upcoming';

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function render()
    {
        $query = Post::query()
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'evento'))
            ->whereNotNull('event_starts_at')
            ->with(['user', 'category'])
            ->withCount(['comments', 'votes']);

        match ($this->filter) {
            'upcoming' => $query->where('event_starts_at', '>=', now())
                ->orderBy('event_starts_at', 'asc'),
            'past' => $query->where('event_starts_at', '<', now())
                ->orderBy('event_starts_at', 'desc'),
            default => $query->orderBy('event_starts_at', 'desc'),
        };

        $events = $query->paginate(12);

        $category = Category::where('slug', 'evento')->first();

        return view('livewire.events.event-list', compact('events', 'category'));
    }
}

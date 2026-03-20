<?php

namespace App\Livewire\Feed;

use App\Models\Category;
use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class FeedList extends Component
{
    use WithPagination;

    public ?int $categoryId = null;

    public bool $neighborhoodOnly = false;

    public function setCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->resetPage();
    }

    public function toggleNeighborhood(): void
    {
        $this->neighborhoodOnly = ! $this->neighborhoodOnly;
        $this->resetPage();
    }

    public function render()
    {
        $userNeighborhood = auth()->user()?->neighborhood;

        $posts = Post::query()
            ->approved()
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->neighborhoodOnly && $userNeighborhood, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('neighborhood', $userNeighborhood)))
            ->with(['user', 'category', 'poll.options', 'poll.votes'])
            ->withCount(['comments', 'votes'])
            ->orderByDesc('is_sponsored')
            ->latest()
            ->paginate(10);

        $categories = Category::query()
            ->whereIn('type', ['post', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.feed.feed-list', compact('posts', 'categories'));
    }
}

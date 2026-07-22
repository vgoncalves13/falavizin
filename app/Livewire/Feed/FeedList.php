<?php

namespace App\Livewire\Feed;

use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class FeedList extends Component
{
    use WithPagination;

    #[Url]
    public ?int $categoryId = null;

    #[Url]
    public bool $neighborhoodOnly = false;

    #[Url]
    public string $sortBy = 'latest';

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

    public function setSortBy(string $sortBy): void
    {
        $this->sortBy = $sortBy;
        $this->resetPage();
    }

    public function render()
    {
        $userNeighborhood = auth()->user()?->neighborhood;

        $posts = Post::query()
            ->approved()
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->neighborhoodOnly && $userNeighborhood, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('neighborhood', $userNeighborhood)))
            ->with(['user', 'category', 'serviceCategory', 'poll.options', 'poll.votes'])
            ->withCount(['comments', 'votes'])
            ->orderByRaw('is_sponsored = 1 AND (sponsored_until IS NULL OR sponsored_until >= NOW()) DESC')
            ->when($this->sortBy === 'trending', fn ($q) => $q->orderByRaw(
                '(votes_count + comments_count * 1.5) / POW(TIMESTAMPDIFF(HOUR, posts.created_at, NOW()) + 2, 1.5) DESC'
            ))
            ->when($this->sortBy === 'latest', fn ($q) => $q->latest())
            ->paginate(10);

        $categories = Category::query()
            ->whereIn('type', ['post', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.feed.feed-list', compact('posts', 'categories'));
    }
}

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

    public function setCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->resetPage();
    }

    public function render()
    {
        $posts = Post::query()
            ->approved()
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
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

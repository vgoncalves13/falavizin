<?php

namespace App\Livewire\Business;

use App\Models\Business;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryId = null;

    public function setCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $businesses = Business::query()
            ->where('status', 'approved')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('neighborhood', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            }))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->with(['category', 'coverPhoto'])
            ->orderByRaw("plan = 'featured' DESC")
            ->orderBy('name')
            ->paginate(12);

        $categories = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.business.business-list', compact('businesses', 'categories'));
    }
}

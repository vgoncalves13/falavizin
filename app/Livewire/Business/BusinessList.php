<?php

namespace App\Livewire\Business;

use App\Models\Business;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryId = null;

    public bool $openNow = false;

    public function setCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->resetPage();
    }

    public function toggleOpenNow(): void
    {
        $this->openNow = ! $this->openNow;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Business::query()
            ->where('status', 'approved')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('neighborhood', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            }))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->with(['category', 'coverPhoto'])
            ->orderByRaw("plan = 'featured' DESC")
            ->orderBy('name');

        if ($this->openNow) {
            $perPage = 12;
            $page = $this->getPage();
            $all = $query->whereNotNull('opening_hours')->get()
                ->filter(fn (Business $b) => $b->isOpenNow() === true)
                ->values();

            $businesses = new LengthAwarePaginator(
                $all->slice(($page - 1) * $perPage, $perPage)->values(),
                $all->count(),
                $perPage,
                $page,
            );
        } else {
            $businesses = $query->paginate(12);
        }

        $categories = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.business.business-list', compact('businesses', 'categories'));
    }
}

<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\NeighborhoodCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class CategoryObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(Category $category): void
    {
        $this->cache->forgetAll();
    }

    public function deleted(Category $category): void
    {
        $this->cache->forgetAll();
    }

    public function restored(Category $category): void
    {
        $this->cache->forgetAll();
    }
}

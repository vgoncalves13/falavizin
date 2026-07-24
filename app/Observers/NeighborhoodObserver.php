<?php

namespace App\Observers;

use App\Models\Neighborhood;
use App\Services\NeighborhoodCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class NeighborhoodObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(Neighborhood $neighborhood): void
    {
        $this->cache->forget($neighborhood);
        $this->cache->forgetActive();
    }

    public function deleted(Neighborhood $neighborhood): void
    {
        $this->cache->forget($neighborhood);
        $this->cache->forgetActive();
    }

    public function restored(Neighborhood $neighborhood): void
    {
        $this->cache->forget($neighborhood);
        $this->cache->forgetActive();
    }
}

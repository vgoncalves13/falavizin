<?php

namespace App\Observers;

use App\Models\Business;
use App\Services\NeighborhoodCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class BusinessObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(Business $business): void
    {
        $this->cache->forget($business->neighborhood_id);
    }

    public function deleted(Business $business): void
    {
        $this->cache->forget($business->neighborhood_id);
    }

    public function restored(Business $business): void
    {
        $this->cache->forget($business->neighborhood_id);
    }
}

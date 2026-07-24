<?php

namespace App\Observers;

use App\Models\Neighborhood;
use App\Services\NeighborhoodCache;
use Illuminate\Support\Facades\Cache;

final class NeighborhoodObserver
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(Neighborhood $neighborhood): void
    {
        $this->cache->forget($neighborhood);
        Cache::forget('neighborhoods:active');
    }

    public function deleted(Neighborhood $neighborhood): void
    {
        $this->cache->forget($neighborhood);
        Cache::forget('neighborhoods:active');
    }

    public function restored(Neighborhood $neighborhood): void
    {
        $this->cache->forget($neighborhood);
        Cache::forget('neighborhoods:active');
    }
}

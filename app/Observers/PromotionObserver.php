<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Services\NeighborhoodCache;

final class PromotionObserver
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(Promotion $promotion): void
    {
        $neighborhoodId = $promotion->business?->neighborhood_id;

        if ($neighborhoodId) {
            $this->cache->forget($neighborhoodId);
        }
    }

    public function deleted(Promotion $promotion): void
    {
        $neighborhoodId = $promotion->business?->neighborhood_id;

        if ($neighborhoodId) {
            $this->cache->forget($neighborhoodId);
        }
    }

    public function restored(Promotion $promotion): void
    {
        $neighborhoodId = $promotion->business?->neighborhood_id;

        if ($neighborhoodId) {
            $this->cache->forget($neighborhoodId);
        }
    }
}

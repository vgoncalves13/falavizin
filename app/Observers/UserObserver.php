<?php

namespace App\Observers;

use App\Models\User;
use App\Services\NeighborhoodCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(User $user): void
    {
        if ($user->wasChanged('neighborhood_id')) {
            $oldId = $user->getOriginal('neighborhood_id');
            $newId = $user->neighborhood_id;

            if ($oldId) {
                $this->cache->forget($oldId);
            }

            if ($newId && $newId !== $oldId) {
                $this->cache->forget($newId);
            }
        }
    }

    public function deleted(User $user): void
    {
        if ($user->neighborhood_id) {
            $this->cache->forget($user->neighborhood_id);
        }
    }

    public function restored(User $user): void
    {
        if ($user->neighborhood_id) {
            $this->cache->forget($user->neighborhood_id);
        }
    }
}

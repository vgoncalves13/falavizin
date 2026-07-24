<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\NeighborhoodCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class PostObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(Post $post): void
    {
        $this->cache->forget($post->neighborhood_id);
    }

    public function deleted(Post $post): void
    {
        $this->cache->forget($post->neighborhood_id);
    }

    public function restored(Post $post): void
    {
        $this->cache->forget($post->neighborhood_id);
    }
}

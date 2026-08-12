<?php

namespace App\Observers;

use App\Actions\CompleteBusinessInitialAction;
use App\Models\Business;
use App\Models\Post;
use App\Services\NeighborhoodCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class PostObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function created(Post $post): void
    {
        $user = $post->user;

        if (! $user || ! $post->neighborhood_id) {
            return;
        }

        $managedBusinessIds = $user->managedBusinesses()
            ->where('businesses.neighborhood_id', $post->neighborhood_id)
            ->pluck('businesses.id');

        if ($managedBusinessIds->isEmpty()) {
            return;
        }

        $action = $post->event_starts_at ? 'event' : 'news';
        $completer = new CompleteBusinessInitialAction;

        foreach ($managedBusinessIds as $businessId) {
            $business = Business::find($businessId);

            if ($business) {
                $completer->execute($business, $user, $action, $post->canonicalUrl());
            }
        }
    }

    public function saved(Post $post): void
    {
        if ($post->neighborhood_id !== null) {
            $this->cache->forget($post->neighborhood_id);
        }
    }

    public function deleted(Post $post): void
    {
        if ($post->neighborhood_id !== null) {
            $this->cache->forget($post->neighborhood_id);
        }
    }

    public function restored(Post $post): void
    {
        if ($post->neighborhood_id !== null) {
            $this->cache->forget($post->neighborhood_id);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use App\Services\NeighborhoodCache;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeCacheTest extends TestCase
{
    use DatabaseMigrations;

    public function test_post_change_invalidates_only_its_neighborhood_cache(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $user = User::factory()->create(['neighborhood_id' => $first->id]);
        $category = Category::factory()->create();
        $cache = app(NeighborhoodCache::class);

        $cache->remember($first, NeighborhoodCache::HOME_POSTS, fn () => 'first');
        $cache->remember($second, NeighborhoodCache::HOME_POSTS, fn () => 'second');

        Post::factory()
            ->for($first, 'neighborhood')
            ->for($user)
            ->for($category)
            ->create();

        $this->assertFalse(Cache::has($cache->key($first, NeighborhoodCache::HOME_POSTS)));
        $this->assertTrue(Cache::has($cache->key($second, NeighborhoodCache::HOME_POSTS)));
    }

    public function test_user_neighborhood_change_invalidates_both_old_and_new(): void
    {
        $oldNeighborhood = Neighborhood::factory()->create();
        $newNeighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create(['neighborhood_id' => $oldNeighborhood->id]);
        $cache = app(NeighborhoodCache::class);

        $cache->remember($oldNeighborhood, NeighborhoodCache::HOME_POSTS, fn () => 'old');
        $cache->remember($newNeighborhood, NeighborhoodCache::HOME_POSTS, fn () => 'new');

        $user->update(['neighborhood_id' => $newNeighborhood->id]);

        $this->assertFalse(Cache::has($cache->key($oldNeighborhood, NeighborhoodCache::HOME_POSTS)));
        $this->assertFalse(Cache::has($cache->key($newNeighborhood, NeighborhoodCache::HOME_POSTS)));
    }

    public function test_category_change_invalidates_all_neighborhoods(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = Category::factory()->create();
        $cache = app(NeighborhoodCache::class);

        $cache->remember($first, NeighborhoodCache::HOME_CATEGORIES, fn () => 'first');
        $cache->remember($second, NeighborhoodCache::HOME_CATEGORIES, fn () => 'second');

        $category->update(['name' => 'Updated']);

        $this->assertFalse(Cache::has($cache->key($first, NeighborhoodCache::HOME_CATEGORIES)));
        $this->assertFalse(Cache::has($cache->key($second, NeighborhoodCache::HOME_CATEGORIES)));
    }
}

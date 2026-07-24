<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeighborhoodModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_neighborhood_exposes_normalized_route_parameters(): void
    {
        $neighborhood = Neighborhood::factory()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'vila-mariana',
        ]);

        $this->assertSame([
            'state' => 'sp',
            'city' => 'sao-paulo',
            'neighborhood' => $neighborhood,
        ], $neighborhood->routeParameters());
    }

    public function test_inactive_neighborhood_rejects_community_interactions(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();

        $this->assertFalse(Post::factory()->for($neighborhood)->create()->acceptsCommunityInteractions());
        $this->assertFalse(
            Business::factory()->create(['neighborhood_id' => $neighborhood->id])
                ->acceptsCommunityInteractions(),
        );
    }

    public function test_active_neighborhood_allows_community_interactions(): void
    {
        $neighborhood = Neighborhood::factory()->create(['is_active' => true]);

        $this->assertTrue(Post::factory()->for($neighborhood)->create()->acceptsCommunityInteractions());
        $this->assertTrue(
            Business::factory()->create(['neighborhood_id' => $neighborhood->id])
                ->acceptsCommunityInteractions(),
        );
    }

    public function test_neighborhood_has_many_users(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        User::factory()->count(3)->create(['neighborhood_id' => $neighborhood->id]);

        $this->assertCount(3, $neighborhood->users);
    }

    public function test_neighborhood_has_many_posts(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        Post::factory()->count(2)->create(['neighborhood_id' => $neighborhood->id]);

        $this->assertCount(2, $neighborhood->posts);
    }

    public function test_neighborhood_has_many_businesses(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        Business::factory()->count(4)->create(['neighborhood_id' => $neighborhood->id]);

        $this->assertCount(4, $neighborhood->businesses);
    }

    public function test_active_scope_filters_active_neighborhoods(): void
    {
        $initialActive = Neighborhood::active()->count();

        Neighborhood::factory()->count(3)->create(['is_active' => true]);
        Neighborhood::factory()->count(2)->create(['is_active' => false]);

        $this->assertCount($initialActive + 3, Neighborhood::active()->get());
    }

    public function test_user_belongs_to_primary_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->assertTrue($user->primaryNeighborhood->is($neighborhood));
    }

    public function test_post_belongs_to_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->assertTrue($post->neighborhood->is($neighborhood));
    }

    public function test_post_for_neighborhood_scope(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        Post::factory()->count(3)->create(['neighborhood_id' => $neighborhood->id]);
        Post::factory()->count(2)->create();

        $this->assertCount(3, Post::forNeighborhood($neighborhood)->get());
    }

    public function test_business_belongs_to_local_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->assertTrue($business->localNeighborhood->is($neighborhood));
    }

    public function test_business_for_neighborhood_scope(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        Business::factory()->count(2)->create(['neighborhood_id' => $neighborhood->id]);
        Business::factory()->count(3)->create();

        $this->assertCount(2, Business::forNeighborhood($neighborhood)->get());
    }

    public function test_neighborhood_is_resolved_by_slug(): void
    {
        $neighborhood = Neighborhood::factory()->create(['slug' => 'copacabana']);

        $this->assertSame('slug', $neighborhood->getRouteKeyName());
    }
}

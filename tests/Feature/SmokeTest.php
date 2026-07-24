<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $this->get(route('home'))->assertStatus(200);
    }

    public function test_feed_index_loads(): void
    {
        $post = Post::factory()->create();

        $this->get(route('neighborhood.feed.index', $post->neighborhood->routeParameters()))->assertStatus(200);
    }

    public function test_feed_show_loads(): void
    {
        $post = Post::factory()->create();

        $this->get($post->canonicalUrl())->assertStatus(200);
    }

    public function test_businesses_index_loads(): void
    {
        $business = Business::factory()->create();

        $this->get(route('neighborhood.businesses.index', $business->localNeighborhood->routeParameters()))->assertStatus(200);
    }

    public function test_businesses_show_loads(): void
    {
        $business = Business::factory()->create();

        $this->get($business->canonicalUrl())->assertStatus(200);
    }

    public function test_promotions_index_loads(): void
    {
        $business = Business::factory()->create();

        $this->get(route('neighborhood.promotions.index', $business->localNeighborhood->routeParameters()))->assertStatus(200);
    }

    public function test_category_show_loads_for_post_type(): void
    {
        $category = Category::factory()->create(['type' => 'post']);
        $post = Post::factory()->create(['category_id' => $category->id]);

        $this->get(route('neighborhood.categories.show', [...$post->neighborhood->routeParameters(), 'category' => $category->slug]))->assertStatus(200);
    }

    public function test_category_show_loads_for_business_type(): void
    {
        $category = Category::factory()->create(['type' => 'business']);
        $business = Business::factory()->create();

        $this->get(route('neighborhood.categories.show', [...$business->localNeighborhood->routeParameters(), 'category' => $category->slug]))->assertStatus(200);
    }

    public function test_admin_moderation_requires_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.moderation.index'))->assertForbidden();
    }

    public function test_admin_moderation_accessible_to_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.moderation.index'))->assertStatus(200);
    }

    public function test_home_page_shows_categories(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        Category::factory()->create(['name' => 'Elétrica', 'type' => 'business']);

        $response = $this->get(route('neighborhood.home', $neighborhood->routeParameters()));

        $response->assertStatus(200)->assertSee('Elétrica');
    }

    public function test_home_page_shows_featured_businesses(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $business = Business::factory()->create(['plan' => 'featured', 'status' => 'approved', 'neighborhood_id' => $neighborhood->id]);

        $response = $this->get(route('neighborhood.home', $neighborhood->routeParameters()));

        $response->assertStatus(200)->assertSee($business->name);
    }

    public function test_home_page_shows_recent_posts(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $post = Post::factory()->create(['status' => 'approved', 'neighborhood_id' => $neighborhood->id]);

        $response = $this->get(route('neighborhood.home', $neighborhood->routeParameters()));

        $response->assertStatus(200)->assertSee($post->title);
    }
}

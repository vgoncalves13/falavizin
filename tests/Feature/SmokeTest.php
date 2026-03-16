<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
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
        $this->get(route('feed.index'))->assertStatus(200);
    }

    public function test_feed_show_loads(): void
    {
        $post = Post::factory()->create();

        $this->get(route('feed.show', $post))->assertStatus(200);
    }

    public function test_businesses_index_loads(): void
    {
        $this->get(route('businesses.index'))->assertStatus(200);
    }

    public function test_businesses_show_loads(): void
    {
        $business = Business::factory()->create();

        $this->get(route('businesses.show', $business))->assertStatus(200);
    }

    public function test_promotions_index_loads(): void
    {
        $this->get(route('promotions.index'))->assertStatus(200);
    }

    public function test_category_show_loads_for_post_type(): void
    {
        $category = Category::factory()->create(['type' => 'post']);

        $this->get(route('categories.show', $category))->assertStatus(200);
    }

    public function test_category_show_loads_for_business_type(): void
    {
        $category = Category::factory()->create(['type' => 'business']);

        $this->get(route('categories.show', $category))->assertStatus(200);
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
        Category::factory()->create(['name' => 'Elétrica', 'type' => 'business']);

        $response = $this->get(route('home'));

        $response->assertStatus(200)->assertSee('Elétrica');
    }

    public function test_home_page_shows_featured_businesses(): void
    {
        $business = Business::factory()->create(['plan' => 'featured', 'status' => 'approved']);

        $response = $this->get(route('home'));

        $response->assertStatus(200)->assertSee($business->name);
    }

    public function test_home_page_shows_recent_posts(): void
    {
        $post = Post::factory()->create(['status' => 'approved']);

        $response = $this->get(route('home'));

        $response->assertStatus(200)->assertSee($post->title);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\Business;
use App\Models\Neighborhood;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_accessible_to_guests(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
    }

    public function test_sitemap_returns_xml_content_type(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_static_pages(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertSee(route('home'), false);
    }

    public function test_sitemap_contains_approved_posts(): void
    {
        $post = Post::factory()->create([
            'status' => PostStatus::Approved,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertSee($post->canonicalUrl(), false);
    }

    public function test_sitemap_does_not_contain_pending_posts(): void
    {
        $post = Post::factory()->create([
            'status' => PostStatus::Pending,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertDontSee($post->canonicalUrl(), false);
    }

    public function test_sitemap_contains_approved_businesses(): void
    {
        $business = Business::factory()->create([
            'status' => BusinessStatus::Approved,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertSee($business->canonicalUrl(), false);
    }

    public function test_sitemap_does_not_contain_pending_businesses(): void
    {
        $business = Business::factory()->create([
            'status' => BusinessStatus::Pending,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertDontSee($business->canonicalUrl(), false);
    }

    public function test_sitemap_is_valid_xml(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
    }

    public function test_sitemap_contains_only_active_neighborhood_content(): void
    {
        $activePost = Post::factory()->create(['status' => PostStatus::Approved]);
        $inactiveNeighborhood = Neighborhood::factory()->inactive()->create();
        $inactivePost = Post::factory()->create([
            'status' => PostStatus::Approved,
            'neighborhood_id' => $inactiveNeighborhood->id,
        ]);

        $this->get(route('sitemap'))
            ->assertSee($activePost->canonicalUrl(), false)
            ->assertDontSee($inactivePost->canonicalUrl(), false);
    }

    public function test_sitemap_includes_neighborhood_scoped_urls(): void
    {
        $neighborhood = Neighborhood::factory()->active()->create();

        $response = $this->get(route('sitemap'));

        $response->assertSee(route('neighborhood.home', $neighborhood->routeParameters()), false);
        $response->assertSee(route('neighborhood.feed.index', $neighborhood->routeParameters()), false);
        $response->assertSee(route('neighborhood.businesses.index', $neighborhood->routeParameters()), false);
    }
}

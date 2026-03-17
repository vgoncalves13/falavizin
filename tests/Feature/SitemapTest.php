<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\Business;
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
        $response->assertSee(route('feed.index'), false);
        $response->assertSee(route('businesses.index'), false);
        $response->assertSee(route('promotions.index'), false);
    }

    public function test_sitemap_contains_approved_posts(): void
    {
        $post = Post::factory()->create([
            'status' => PostStatus::Approved,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertSee(route('feed.show', $post), false);
    }

    public function test_sitemap_does_not_contain_pending_posts(): void
    {
        $post = Post::factory()->create([
            'status' => PostStatus::Pending,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertDontSee(route('feed.show', $post), false);
    }

    public function test_sitemap_contains_approved_businesses(): void
    {
        $business = Business::factory()->create([
            'status' => BusinessStatus::Approved,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertSee(route('businesses.show', $business), false);
    }

    public function test_sitemap_does_not_contain_pending_businesses(): void
    {
        $business = Business::factory()->create([
            'status' => BusinessStatus::Pending,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertDontSee(route('businesses.show', $business), false);
    }

    public function test_sitemap_is_valid_xml(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
    }
}

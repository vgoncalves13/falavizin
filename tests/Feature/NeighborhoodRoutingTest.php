<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Neighborhood;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeighborhoodRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_route_resolves_the_full_neighborhood_path(): void
    {
        $neighborhood = Neighborhood::factory()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'pinheiros',
        ]);

        $this->get(route('neighborhood.home', $neighborhood->routeParameters()))
            ->assertOk()
            ->assertViewHas('neighborhood', $neighborhood);
    }

    public function test_post_under_the_wrong_neighborhood_returns_not_found(): void
    {
        $correct = Neighborhood::factory()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'pinheiros',
        ]);
        $wrong = Neighborhood::factory()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'vila-mariana',
        ]);
        $post = Post::factory()->create(['neighborhood_id' => $correct->id]);

        $this->get(route('neighborhood.feed.show', [
            ...$wrong->routeParameters(),
            'post' => $post,
        ]))->assertNotFound();
    }

    public function test_legacy_post_url_redirects_permanently_to_canonical_url(): void
    {
        $neighborhood = Neighborhood::factory()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'pinheiros',
        ]);
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('feed.show', $post))
            ->assertRedirect($post->canonicalUrl())
            ->assertStatus(301);
    }

    public function test_models_generate_urls_from_their_own_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'pinheiros',
        ]);
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->assertStringContainsString('/feed/', $post->canonicalUrl());
        $this->assertStringContainsString('/servicos/', $business->canonicalUrl());
    }

    public function test_inactive_neighborhood_returns_not_found(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'bairro-inativo',
        ]);

        $this->get(route('neighborhood.home', $neighborhood->routeParameters()))
            ->assertNotFound();
    }

    public function test_legacy_feed_index_redirects_to_neighborhood_feed(): void
    {
        $neighborhood = Neighborhood::factory()->create([
            'state_code' => 'MG',
            'city_slug' => 'belo-horizonte',
            'slug' => 'savassi',
            'sort_order' => 1,
        ]);

        $this->withCookie('last_neighborhood_id', (string) $neighborhood->id)
            ->get(route('feed.index'))
            ->assertStatus(302)
            ->assertRedirect(route('neighborhood.feed.index', $neighborhood->routeParameters()));
    }

    public function test_legacy_businesses_index_redirects_to_neighborhood_businesses(): void
    {
        $neighborhood = Neighborhood::factory()->create([
            'state_code' => 'MG',
            'city_slug' => 'belo-horizonte',
            'slug' => 'savassi',
            'sort_order' => 1,
        ]);

        $this->withCookie('last_neighborhood_id', (string) $neighborhood->id)
            ->get(route('businesses.index'))
            ->assertStatus(302)
            ->assertRedirect(route('neighborhood.businesses.index', $neighborhood->routeParameters()));
    }

    public function test_legacy_business_show_redirects_permanently_to_canonical_url(): void
    {
        $neighborhood = Neighborhood::factory()->create([
            'state_code' => 'SP',
            'city_slug' => 'sao-paulo',
            'slug' => 'pinheiros',
        ]);
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('businesses.show', $business))
            ->assertRedirect($business->canonicalUrl())
            ->assertStatus(301);
    }
}

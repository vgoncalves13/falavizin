<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeighborhoodContentIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_home_never_shows_content_from_another_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = Category::factory()->create(['slug' => 'aviso', 'type' => 'post']);

        $visible = Post::factory()->create([
            'neighborhood_id' => $first->id,
            'category_id' => $category->id,
            'title' => 'Post visivel no primeiro bairro',
        ]);

        $hidden = Post::factory()->create([
            'neighborhood_id' => $second->id,
            'category_id' => $category->id,
            'title' => 'Post oculto do segundo bairro',
        ]);

        $this->get(route('neighborhood.home', $first->routeParameters()))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_local_feed_never_shows_posts_from_another_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = Category::factory()->create(['slug' => 'aviso', 'type' => 'post']);

        $visible = Post::factory()->create([
            'neighborhood_id' => $first->id,
            'category_id' => $category->id,
            'title' => 'Feed visivel no primeiro',
        ]);

        $hidden = Post::factory()->create([
            'neighborhood_id' => $second->id,
            'category_id' => $category->id,
            'title' => 'Feed oculto do segundo',
        ]);

        $this->get(route('neighborhood.feed.index', $first->routeParameters()))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_local_search_never_shows_results_from_another_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = Category::factory()->create(['slug' => 'aviso', 'type' => 'post']);

        Post::factory()->create([
            'neighborhood_id' => $second->id,
            'category_id' => $category->id,
            'title' => 'Vazamento de agua no segundo bairro',
        ]);

        $this->get(route('neighborhood.search.index', [
            ...$first->routeParameters(),
            'q' => 'vazamento',
        ]))->assertDontSee('Vazamento de agua no segundo bairro');
    }

    public function test_local_category_never_shows_posts_from_another_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = Category::factory()->create(['slug' => 'aviso', 'type' => 'post']);

        $visible = Post::factory()->create([
            'neighborhood_id' => $first->id,
            'category_id' => $category->id,
            'title' => 'Categoria visivel no primeiro',
        ]);

        $hidden = Post::factory()->create([
            'neighborhood_id' => $second->id,
            'category_id' => $category->id,
            'title' => 'Categoria oculta do segundo',
        ]);

        $this->get(route('neighborhood.categories.show', [
            ...$first->routeParameters(),
            'category' => $category->slug,
        ]))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_local_events_never_shows_events_from_another_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $eventCategory = Category::factory()->create(['slug' => 'evento', 'type' => 'post']);

        $visible = Post::factory()->create([
            'neighborhood_id' => $first->id,
            'category_id' => $eventCategory->id,
            'title' => 'Evento visivel no primeiro',
            'event_starts_at' => now()->addDays(3),
        ]);

        $hidden = Post::factory()->create([
            'neighborhood_id' => $second->id,
            'category_id' => $eventCategory->id,
            'title' => 'Evento oculto do segundo',
            'event_starts_at' => now()->addDays(3),
        ]);

        $this->get(route('neighborhood.events.index', $first->routeParameters()))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_post_show_related_posts_limited_to_same_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $otherNeighborhood = Neighborhood::factory()->create();
        $category = Category::factory()->create(['slug' => 'aviso', 'type' => 'post']);

        $post = Post::factory()->create([
            'neighborhood_id' => $neighborhood->id,
            'category_id' => $category->id,
        ]);

        $related = Post::factory()->create([
            'neighborhood_id' => $neighborhood->id,
            'category_id' => $category->id,
            'title' => 'Relacionado mesmo bairro',
        ]);

        $otherRelated = Post::factory()->create([
            'neighborhood_id' => $otherNeighborhood->id,
            'category_id' => $category->id,
            'title' => 'Relacionado outro bairro',
        ]);

        $this->get(route('neighborhood.feed.show', [
            ...$neighborhood->routeParameters(),
            'post' => $post->slug,
        ]))
            ->assertSee($related->title)
            ->assertDontSee($otherRelated->title);
    }
}

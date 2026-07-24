<?php

namespace Tests\Feature;

use App\Actions\CreatePostAction;
use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\User;
use App\Notifications\NewRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NeighborhoodBusinessIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $slug, array $overrides = []): Category
    {
        return Category::firstOrCreate(['slug' => $slug], array_merge([
            'name' => ucfirst($slug),
            'icon' => 'bolt',
            'type' => 'both',
            'sort_order' => 99,
        ], $overrides));
    }

    public function test_business_map_is_scoped_to_the_route_neighborhood(): void
    {
        $first = Neighborhood::factory()->create(['latitude' => -22.90, 'longitude' => -43.30]);
        $second = Neighborhood::factory()->create();
        $visible = Business::factory()->create(['neighborhood_id' => $first->id, 'lat' => -22.90, 'lng' => -43.30]);
        $hidden = Business::factory()->create(['neighborhood_id' => $second->id, 'lat' => -22.90, 'lng' => -43.30]);

        $this->getJson(route('neighborhood.businesses.map', [
            ...$first->routeParameters(),
            'north' => -22,
            'south' => -23,
            'east' => -43,
            'west' => -44,
        ]))
            ->assertJsonFragment(['id' => $visible->id])
            ->assertJsonMissing(['id' => $hidden->id]);
    }

    public function test_business_map_uses_canonical_url(): void
    {
        $neighborhood = Neighborhood::factory()->create(['latitude' => -22.90, 'longitude' => -43.30]);
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id, 'lat' => -22.90, 'lng' => -43.30]);

        $response = $this->getJson(route('neighborhood.businesses.map', [
            ...$neighborhood->routeParameters(),
            'north' => -22,
            'south' => -23,
            'east' => -43,
            'west' => -44,
        ]));

        $response->assertJsonFragment([
            'id' => $business->id,
            'url' => $business->canonicalUrl(),
        ]);
    }

    public function test_business_map_includes_neighborhood_name_from_model(): void
    {
        $neighborhood = Neighborhood::factory()->create(['name' => 'Copacabana', 'latitude' => -22.90, 'longitude' => -43.30]);
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id, 'lat' => -22.90, 'lng' => -43.30]);

        $response = $this->getJson(route('neighborhood.businesses.map', [
            ...$neighborhood->routeParameters(),
            'north' => -22,
            'south' => -23,
            'east' => -43,
            'west' => -44,
        ]));

        $response->assertJsonFragment([
            'id' => $business->id,
            'neighborhood' => 'Copacabana',
        ]);
    }

    public function test_promotions_index_is_scoped_to_the_route_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $businessFirst = Business::factory()->create(['neighborhood_id' => $first->id]);
        $businessSecond = Business::factory()->create(['neighborhood_id' => $second->id]);
        $visible = Promotion::factory()->create(['business_id' => $businessFirst->id]);
        $hidden = Promotion::factory()->create(['business_id' => $businessSecond->id]);

        $this->get(route('neighborhood.promotions.index', $first->routeParameters()))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_pulso_posts_by_category_are_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = $this->category('aviso');

        Post::factory()->count(3)->create(['neighborhood_id' => $first->id, 'category_id' => $category->id]);
        Post::factory()->count(2)->create(['neighborhood_id' => $second->id, 'category_id' => $category->id]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee('3');
    }

    public function test_pulso_top_problems_are_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = $this->category('problema');

        $visible = Post::factory()->create(['neighborhood_id' => $first->id, 'category_id' => $category->id]);
        $hidden = Post::factory()->create(['neighborhood_id' => $second->id, 'category_id' => $category->id]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_pulso_resolved_this_week_is_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = $this->category('problema');

        Post::factory()->count(2)->create([
            'neighborhood_id' => $first->id,
            'category_id' => $category->id,
            'resolution_status' => 'resolvido',
            'resolved_at' => now(),
        ]);
        Post::factory()->count(3)->create([
            'neighborhood_id' => $second->id,
            'category_id' => $category->id,
            'resolution_status' => 'resolvido',
            'resolved_at' => now(),
        ]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee('2');
    }

    public function test_pulso_open_problems_are_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = $this->category('problema');

        Post::factory()->count(2)->create([
            'neighborhood_id' => $first->id,
            'category_id' => $category->id,
            'resolution_status' => null,
        ]);
        Post::factory()->count(5)->create([
            'neighborhood_id' => $second->id,
            'category_id' => $category->id,
            'resolution_status' => null,
        ]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee('2');
    }

    public function test_pulso_top_business_is_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();

        $visible = Business::factory()->create(['neighborhood_id' => $first->id, 'status' => BusinessStatus::Approved]);
        $hidden = Business::factory()->create(['neighborhood_id' => $second->id, 'status' => BusinessStatus::Approved]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee($visible->name)
            ->assertDontSee($hidden->name);
    }

    public function test_pulso_posts_this_week_is_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();

        Post::factory()->count(4)->create(['neighborhood_id' => $first->id]);
        Post::factory()->count(6)->create(['neighborhood_id' => $second->id]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee('4');
    }

    public function test_pulso_active_requests_are_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();
        $category = $this->category('pedido', ['type' => 'post']);

        $visible = Post::factory()->create(['neighborhood_id' => $first->id, 'category_id' => $category->id]);
        $hidden = Post::factory()->create(['neighborhood_id' => $second->id, 'category_id' => $category->id]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_pulso_active_businesses_count_is_scoped_to_neighborhood(): void
    {
        $first = Neighborhood::factory()->create();
        $second = Neighborhood::factory()->create();

        Business::factory()->count(3)->create(['neighborhood_id' => $first->id, 'status' => BusinessStatus::Approved]);
        Business::factory()->count(5)->create(['neighborhood_id' => $second->id, 'status' => BusinessStatus::Approved]);

        $this->get(route('neighborhood.pulso.index', $first->routeParameters()))
            ->assertSee('3');
    }

    public function test_profile_relevant_requests_scoped_to_user_business_neighborhoods(): void
    {
        $user = User::factory()->create();
        $neighborhoodA = Neighborhood::factory()->create();
        $neighborhoodB = Neighborhood::factory()->create();
        $category = $this->category('service-cat');
        $pedidoCategory = $this->category('pedido', ['type' => 'post']);

        $business = Business::factory()->create([
            'user_id' => $user->id,
            'neighborhood_id' => $neighborhoodA->id,
        ]);
        $business->categories()->attach($category);

        $visible = Post::factory()->create([
            'neighborhood_id' => $neighborhoodA->id,
            'service_category_id' => $category->id,
            'category_id' => $pedidoCategory->id,
        ]);
        $hidden = Post::factory()->create([
            'neighborhood_id' => $neighborhoodB->id,
            'service_category_id' => $category->id,
            'category_id' => $pedidoCategory->id,
        ]);

        $this->actingAs($user)
            ->get(route('profile.account', ['tab' => 'requests']))
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);
    }

    public function test_new_request_notification_recipients_filtered_by_neighborhood(): void
    {
        Notification::fake();

        $neighborhood = Neighborhood::factory()->create();
        $otherNeighborhood = Neighborhood::factory()->create();
        $pedidoCategory = $this->category('pedido', ['type' => 'post']);
        $serviceCategory = $this->category('service-cat-notify', ['type' => 'business']);

        $merchantInNeighborhood = User::factory()->create();
        $businessIn = Business::factory()->create([
            'user_id' => $merchantInNeighborhood->id,
            'neighborhood_id' => $neighborhood->id,
        ]);
        $businessIn->categories()->attach($serviceCategory);

        $merchantOutside = User::factory()->create();
        $businessOut = Business::factory()->create([
            'user_id' => $merchantOutside->id,
            'neighborhood_id' => $otherNeighborhood->id,
        ]);
        $businessOut->categories()->attach($serviceCategory);

        $postAuthor = User::factory()->create();

        (new CreatePostAction)->execute(
            user: $postAuthor,
            neighborhood: $neighborhood,
            data: [
                'category_id' => $pedidoCategory->id,
                'service_category_id' => $serviceCategory->id,
                'title' => 'Preciso de eletricista',
                'body' => 'Busco eletricista para instalar ventilador.',
            ],
        );

        Notification::assertSentTo($merchantInNeighborhood, NewRequestNotification::class);
        Notification::assertNotSentTo($merchantOutside, NewRequestNotification::class);
    }
}

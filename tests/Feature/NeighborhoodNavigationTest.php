<?php

namespace Tests\Feature;

use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeighborhoodNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_navigation_shows_the_current_neighborhood_twice_for_responsive_layouts(): void
    {
        $neighborhood = Neighborhood::factory()->create(['name' => 'Engenho da Rainha']);

        $this->get(route('neighborhood.home', $neighborhood->routeParameters()))
            ->assertOk()
            ->assertSee('Engenho da Rainha', false)
            ->assertSee('data-desktop-navigation', false)
            ->assertSee('data-mobile-menu-button', false)
            ->assertSee('data-neighborhood-switcher-desktop', false)
            ->assertSee('data-neighborhood-switcher-mobile', false);
    }

    public function test_navigation_shows_active_neighborhoods_in_switcher(): void
    {
        $active = Neighborhood::factory()->create(['name' => 'Copacabana', 'is_active' => true]);
        Neighborhood::factory()->inactive()->create(['name' => 'Inativo']);

        $this->get(route('neighborhood.home', $active->routeParameters()))
            ->assertOk()
            ->assertSee('Copacabana', false)
            ->assertDontSee('Inativo', false);
    }

    public function test_authenticated_user_sees_make_primary_button_in_switcher(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->create();

        $this->actingAs($user)
            ->get(route('neighborhood.home', $neighborhood->routeParameters()))
            ->assertOk()
            ->assertSee('Tornar meu bairro principal', false);
    }

    public function test_profile_update_accepts_neighborhood_id(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->create(['name' => 'Tijuca']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'neighborhood_id' => $neighborhood->id,
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertEquals($neighborhood->id, $user->neighborhood_id);
        $this->assertSame('Tijuca', $user->neighborhood);
    }

    public function test_profile_update_rejects_inactive_neighborhood_id(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->inactive()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'neighborhood_id' => $neighborhood->id,
            ])
            ->assertSessionHasErrors('neighborhood_id');
    }
}

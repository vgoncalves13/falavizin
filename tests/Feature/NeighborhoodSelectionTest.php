<?php

namespace Tests\Feature;

use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeighborhoodSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_root_lists_active_neighborhoods_and_continue_shortcut(): void
    {
        $active = Neighborhood::factory()->create(['name' => 'Engenho da Rainha']);
        $inactive = Neighborhood::factory()->inactive()->create(['name' => 'Inativo']);

        $this->withCookie('last_neighborhood_id', (string) $active->id)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Engenho da Rainha')
            ->assertSee('Continuar em Engenho da Rainha')
            ->assertDontSee('Inativo');
    }

    public function test_switching_route_does_not_change_primary_neighborhood(): void
    {
        $primary = Neighborhood::factory()->create();
        $visited = Neighborhood::factory()->create();
        $user = User::factory()->create(['neighborhood_id' => $primary->id]);

        $this->actingAs($user)
            ->get(route('neighborhood.home', $visited->routeParameters()))
            ->assertOk();

        $this->assertSame($primary->id, $user->refresh()->neighborhood_id);
    }

    public function test_authenticated_user_with_active_primary_neighborhood_redirects_to_neighborhood_home(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('neighborhood.home', $neighborhood->routeParameters()));
    }

    public function test_authenticated_user_without_primary_neighborhood_sees_directory(): void
    {
        $neighborhood = Neighborhood::factory()->create(['name' => 'Tijuca']);
        $user = User::factory()->create(['neighborhood_id' => null]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Tijuca');
    }

    public function test_authenticated_user_with_inactive_primary_neighborhood_sees_directory(): void
    {
        $inactive = Neighborhood::factory()->inactive()->create();
        $active = Neighborhood::factory()->create(['name' => 'Copacabana']);
        $user = User::factory()->create(['neighborhood_id' => $inactive->id]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Copacabana');
    }

    public function test_select_page_requires_authentication(): void
    {
        $this->get(route('neighborhoods.select'))
            ->assertRedirect(route('login'));
    }

    public function test_select_page_lists_active_neighborhoods(): void
    {
        $active = Neighborhood::factory()->create(['name' => 'Copacabana']);
        $inactive = Neighborhood::factory()->inactive()->create(['name' => 'Inativo']);
        $user = User::factory()->create(['neighborhood_id' => null]);

        $this->actingAs($user)
            ->get(route('neighborhoods.select'))
            ->assertOk()
            ->assertSee('Copacabana')
            ->assertDontSee('Inativo');
    }

    public function test_update_sets_primary_neighborhood_and_legacy_field(): void
    {
        $neighborhood = Neighborhood::factory()->create(['name' => 'Tijuca']);
        $user = User::factory()->create(['neighborhood_id' => null, 'neighborhood' => '']);

        $this->actingAs($user)
            ->patch(route('neighborhoods.update'), ['neighborhood_id' => $neighborhood->id])
            ->assertRedirect(route('neighborhood.home', $neighborhood->routeParameters()));

        $user->refresh();
        $this->assertSame($neighborhood->id, $user->neighborhood_id);
        $this->assertSame('Tijuca', $user->neighborhood);
    }

    public function test_update_rejects_inactive_neighborhood(): void
    {
        $inactive = Neighborhood::factory()->inactive()->create();
        $user = User::factory()->create(['neighborhood_id' => null]);

        $this->actingAs($user)
            ->patch(route('neighborhoods.update'), ['neighborhood_id' => $inactive->id])
            ->assertSessionHasErrors('neighborhood_id');
    }

    public function test_update_rejects_nonexistent_neighborhood(): void
    {
        $user = User::factory()->create(['neighborhood_id' => null]);

        $this->actingAs($user)
            ->patch(route('neighborhoods.update'), ['neighborhood_id' => 9999])
            ->assertSessionHasErrors('neighborhood_id');
    }

    public function test_update_requires_authentication(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $this->patch(route('neighborhoods.update'), ['neighborhood_id' => $neighborhood->id])
            ->assertRedirect(route('login'));
    }

    public function test_guest_root_without_cookie_shows_directory(): void
    {
        $neighborhood = Neighborhood::factory()->create(['name' => 'Barra da Tijuca']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Barra da Tijuca');
    }

    public function test_directory_page_is_accessible(): void
    {
        $n1 = Neighborhood::factory()->create(['sort_order' => 2]);
        $n2 = Neighborhood::factory()->create(['sort_order' => 1]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($n1->name)
            ->assertSee($n2->name);
    }

    public function test_primary_neighborhood_middleware_blocks_user_without_active_neighborhood(): void
    {
        $user = User::factory()->create(['neighborhood_id' => null]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('neighborhoods.select'));
    }

    public function test_primary_neighborhood_middleware_allows_user_with_active_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();
    }
}

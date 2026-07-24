<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\NeighborhoodManager;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NeighborhoodManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_neighborhood_manager(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->assertStatus(200);
    }

    public function test_non_admin_receives_403(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        Livewire::actingAs($user)
            ->test(NeighborhoodManager::class)
            ->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        Livewire::test(NeighborhoodManager::class)
            ->assertStatus(403);
    }

    public function test_admin_can_create_neighborhood(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Copacabana')
            ->set('city', 'Rio de Janeiro')
            ->set('stateCode', 'RJ')
            ->set('latitude', '-22.9711')
            ->set('longitude', '-43.1823')
            ->set('sortOrder', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('neighborhoods', [
            'name' => 'Copacabana',
            'slug' => 'copacabana',
            'city' => 'Rio de Janeiro',
            'city_slug' => 'rio-de-janeiro',
            'state_code' => 'RJ',
            'is_active' => true,
        ]);
    }

    public function test_state_code_is_normalized_to_uppercase(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Botafogo')
            ->set('city', 'Rio de Janeiro')
            ->set('stateCode', 'rj')
            ->call('save');

        $this->assertDatabaseHas('neighborhoods', [
            'name' => 'Botafogo',
            'state_code' => 'RJ',
        ]);
    }

    public function test_slug_is_generated_from_name_when_empty(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'São Cristóvão')
            ->set('city', 'Rio de Janeiro')
            ->set('stateCode', 'RJ')
            ->call('save');

        $this->assertDatabaseHas('neighborhoods', [
            'name' => 'São Cristóvão',
            'slug' => 'sao-cristovao',
        ]);
    }

    public function test_city_slug_is_generated_from_city_when_empty(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Lapa')
            ->set('city', 'São Paulo')
            ->set('stateCode', 'SP')
            ->call('save');

        $this->assertDatabaseHas('neighborhoods', [
            'city' => 'São Paulo',
            'city_slug' => 'sao-paulo',
        ]);
    }

    public function test_duplicate_neighborhood_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Neighborhood::factory()->create([
            'name' => 'Copacabana',
            'slug' => 'copacabana',
            'city' => 'Rio de Janeiro',
            'city_slug' => 'rio-de-janeiro',
            'state_code' => 'RJ',
        ]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Copacabana')
            ->set('city', 'Rio de Janeiro')
            ->set('stateCode', 'RJ')
            ->call('save')
            ->assertHasErrors('name');
    }

    public function test_admin_can_edit_neighborhood(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $neighborhood = Neighborhood::factory()->create([
            'name' => 'Copacabana',
            'city' => 'Rio de Janeiro',
            'state_code' => 'RJ',
            'latitude' => -22.9711,
            'longitude' => -43.1823,
        ]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->call('edit', $neighborhood->id)
            ->assertSee('data-neighborhood-map', false)
            ->assertSee('Buscar bairro no mapa')
            ->assertSet('latitude', '-22.97110000')
            ->assertSet('longitude', '-43.18230000')
            ->set('name', 'Copacabana Atualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('neighborhoods', [
            'id' => $neighborhood->id,
            'name' => 'Copacabana Atualizado',
        ]);
    }

    public function test_slug_preserved_after_content_exists(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $neighborhood = Neighborhood::factory()->create([
            'name' => 'Copacabana',
            'slug' => 'copacabana',
            'city' => 'Rio de Janeiro',
            'city_slug' => 'rio-de-janeiro',
            'state_code' => 'RJ',
        ]);
        Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->call('edit', $neighborhood->id)
            ->set('name', 'Copacabana Novo')
            ->set('slug', 'copacabana-novo')
            ->set('city', 'Niterói')
            ->set('citySlug', 'niteroi')
            ->set('stateCode', 'MG')
            ->call('save')
            ->assertHasNoErrors();

        $neighborhood->refresh();
        $this->assertSame('copacabana', $neighborhood->slug);
        $this->assertSame('Rio de Janeiro', $neighborhood->city);
        $this->assertSame('rio-de-janeiro', $neighborhood->city_slug);
        $this->assertSame('RJ', $neighborhood->state_code);
    }

    public function test_admin_can_deactivate_neighborhood(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Neighborhood::factory()->create(['is_active' => true]);
        $target = Neighborhood::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->call('toggleActive', $target->id)
            ->assertHasNoErrors();

        $this->assertFalse($target->refresh()->is_active);
    }

    public function test_admin_can_activate_neighborhood(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Neighborhood::factory()->create(['is_active' => true]);
        $target = Neighborhood::factory()->inactive()->create();

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->call('toggleActive', $target->id)
            ->assertHasNoErrors();

        $this->assertTrue($target->refresh()->is_active);
    }

    public function test_admin_cannot_deactivate_the_last_active_neighborhood(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        // Deactivate all existing neighborhoods except one
        Neighborhood::query()->update(['is_active' => false]);
        $neighborhood = Neighborhood::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->call('toggleActive', $neighborhood->id)
            ->assertHasErrors('status');

        $this->assertTrue($neighborhood->refresh()->is_active);
    }

    public function test_state_code_must_be_two_characters(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Teste')
            ->set('city', 'Cidade')
            ->set('stateCode', 'A')
            ->call('save')
            ->assertHasErrors('state_code');
    }

    public function test_latitude_must_be_in_valid_range(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Teste')
            ->set('city', 'Cidade')
            ->set('stateCode', 'SP')
            ->set('latitude', '999')
            ->call('save')
            ->assertHasErrors('latitude');
    }

    public function test_longitude_must_be_in_valid_range(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Teste')
            ->set('city', 'Cidade')
            ->set('stateCode', 'SP')
            ->set('longitude', '-999')
            ->call('save')
            ->assertHasErrors('longitude');
    }

    public function test_sort_order_must_be_non_negative(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->set('showForm', true)
            ->set('name', 'Teste')
            ->set('city', 'Cidade')
            ->set('stateCode', 'SP')
            ->set('sortOrder', -5)
            ->call('save')
            ->assertHasErrors('sort_order');
    }

    public function test_businesses_safeguard_slug_city_and_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $neighborhood = Neighborhood::factory()->create([
            'name' => 'Centro',
            'slug' => 'centro',
            'city' => 'Rio de Janeiro',
            'city_slug' => 'rio-de-janeiro',
            'state_code' => 'RJ',
        ]);
        $neighborhood->businesses()->create([
            'user_id' => null,
            'category_id' => 1,
            'name' => 'Bar do Zé',
            'slug' => 'bar-do-ze',
            'neighborhood' => 'Centro',
        ]);

        Livewire::actingAs($admin)
            ->test(NeighborhoodManager::class)
            ->call('edit', $neighborhood->id)
            ->set('slug', 'centro-novo')
            ->set('city', 'Niterói')
            ->set('citySlug', 'niteroi')
            ->set('stateCode', 'MG')
            ->call('save')
            ->assertHasNoErrors();

        $neighborhood->refresh();
        $this->assertSame('centro', $neighborhood->slug);
        $this->assertSame('Rio de Janeiro', $neighborhood->city);
        $this->assertSame('rio-de-janeiro', $neighborhood->city_slug);
        $this->assertSame('RJ', $neighborhood->state_code);
    }
}

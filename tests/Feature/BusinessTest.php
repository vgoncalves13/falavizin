<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Livewire\Business\BusinessForm;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_businesses_index_is_accessible_to_guests(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->get(route('neighborhood.businesses.index', $neighborhood->routeParameters()));

        $response->assertStatus(200);
    }

    public function test_map_endpoint_requires_valid_bounds(): void
    {
        $this->getJson(route('businesses.map'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['north', 'south', 'east', 'west']);
    }

    public function test_map_endpoint_limits_results_to_visible_bounds(): void
    {
        $category = Category::factory()->create(['type' => 'business']);
        Business::factory()->count(201)->create([
            'category_id' => $category->id,
            'status' => BusinessStatus::Approved,
            'lat' => -22.90,
            'lng' => -43.20,
        ]);
        $outside = Business::factory()->create([
            'category_id' => $category->id,
            'status' => BusinessStatus::Approved,
            'lat' => -23.50,
            'lng' => -44.00,
        ]);

        $response = $this->getJson(route('businesses.map', [
            'north' => -22.80,
            'south' => -23.00,
            'east' => -43.10,
            'west' => -43.30,
        ]));

        $response->assertOk()
            ->assertJsonCount(200, 'data')
            ->assertJsonPath('truncated', true)
            ->assertJsonMissing(['id' => $outside->id]);
    }

    public function test_business_show_is_accessible_to_guests(): void
    {
        $business = Business::factory()->create();

        $response = $this->get(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_pending_business(): void
    {
        $business = Business::factory()->create(['status' => BusinessStatus::Pending]);

        $this->get(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))->assertForbidden();
    }

    public function test_owner_and_admin_can_view_pending_business(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $business = Business::factory()->create([
            'user_id' => $owner->id,
            'status' => BusinessStatus::Pending,
        ]);

        $this->actingAs($owner)->get(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))->assertOk();
        $this->actingAs($admin)->get(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))->assertOk();
    }

    public function test_map_popups_render_business_data_as_text(): void
    {
        $indexView = file_get_contents(resource_path('views/businesses/index.blade.php'));
        $showView = file_get_contents(resource_path('views/businesses/show.blade.php'));

        $this->assertStringContainsString('name.textContent = b.name;', $indexView);
        $this->assertStringContainsString('location.textContent =', $indexView);
        $this->assertStringNotContainsString('${b.name}', $indexView);
        $this->assertStringContainsString('popup.textContent = name;', $showView);
        $this->assertStringNotContainsString('<strong>${name}</strong>', $showView);
    }

    public function test_create_business_page_requires_authentication(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->get(route('neighborhood.businesses.create', $neighborhood->routeParameters()));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_create_business_page(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->actingAs($user)->get(route('neighborhood.businesses.create', $neighborhood->routeParameters()));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_business(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->actingAs($user)->post(route('neighborhood.businesses.store', $neighborhood->routeParameters()), [
            'name' => 'Padaria do João',
            'category_ids' => [$category->id],
            'description' => 'A melhor padaria do bairro.',
            'phone' => '(21) 3333-4444',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'name' => 'Padaria do João',
            'user_id' => $user->id,
            'neighborhood_id' => $neighborhood->id,
            'status' => BusinessStatus::Pending->value,
            'claimed' => true,
        ]);
        $this->assertSame(['(21) 3333-4444'], Business::where('name', 'Padaria do João')->firstOrFail()->phone);
    }

    public function test_business_form_creates_business_with_essential_fields_only(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);
        $neighborhood = Neighborhood::factory()->create();

        Livewire::actingAs($user)
            ->test(BusinessForm::class, ['neighborhood' => $neighborhood])
            ->set('name', 'Mercado 24 Horas')
            ->set('categoryIds', [$category->id])
            ->set('whatsapp', '(21) 9 9999-9999')
            ->call('save');

        $business = Business::where('name', 'Mercado 24 Horas')->firstOrFail();
        $this->assertSame($neighborhood->name, $business->neighborhood);
        $this->assertSame($neighborhood->id, $business->neighborhood_id);
        $this->assertSame('(21) 9 9999-9999', $business->whatsapp);
    }

    public function test_business_is_open_after_midnight_when_previous_day_closes_later(): void
    {
        $business = Business::factory()->make(['opening_hours' => [
            ['day' => 'Segunda-feira', 'open' => '22:00', 'close' => '02:00', 'closed' => false],
        ]]);

        $this->travelTo('2026-07-21 01:00:00');
        $this->assertTrue($business->isOpenNow());

        $this->travelTo('2026-07-21 03:00:00');
        $this->assertFalse($business->isOpenNow());
    }

    public function test_business_store_requires_authentication(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->post(route('neighborhood.businesses.store', $neighborhood->routeParameters()), [
            'name' => 'Padaria do João',
            'category_ids' => [1],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_business_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->actingAs($user)->post(route('neighborhood.businesses.store', $neighborhood->routeParameters()), []);

        $response->assertSessionHasErrors(['name', 'category_ids']);
    }

    public function test_owner_can_edit_business(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('businesses.edit', $business));

        $response->assertStatus(200);
    }

    public function test_non_owner_cannot_edit_business(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->get(route('businesses.edit', $business));

        $response->assertForbidden();
    }

    public function test_non_owner_cannot_update_business_through_livewire(): void
    {
        $business = Business::factory()->create();
        $neighborhood = $business->localNeighborhood;

        Livewire::actingAs(User::factory()->create())
            ->test(BusinessForm::class, ['business' => $business, 'neighborhood' => $neighborhood])
            ->set('name', 'Alteração indevida')
            ->call('save')
            ->assertForbidden();

        $this->assertNotSame('Alteração indevida', $business->fresh()->name);
    }

    public function test_owner_can_update_business(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);
        $business = Business::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('businesses.update', $business), [
            'name' => 'Padaria do João Atualizada',
            'category_ids' => [$category->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', ['name' => 'Padaria do João Atualizada']);
    }

    public function test_admin_can_update_any_business(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create(['type' => 'business']);
        $business = Business::factory()->create();

        $response = $this->actingAs($admin)->put(route('businesses.update', $business), [
            'name' => 'Editado pelo Admin',
            'category_ids' => [$category->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', ['name' => 'Editado pelo Admin']);
    }

    public function test_promotions_index_is_accessible_to_guests(): void
    {
        $response = $this->get(route('promotions.index'));

        $response->assertStatus(200);
    }

    public function test_owner_can_create_promotion(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('neighborhood.promotions.store', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]), [
            'title' => '30% de desconto este final de semana',
            'description' => 'Promoção especial de feriado.',
        ]);

        $response->assertRedirect(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]));
        $this->assertDatabaseHas('promotions', [
            'business_id' => $business->id,
            'title' => '30% de desconto este final de semana',
        ]);
    }

    public function test_non_owner_cannot_create_promotion(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->post(route('neighborhood.promotions.store', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]), [
            'title' => '30% de desconto este final de semana',
        ]);

        $response->assertForbidden();
    }

    public function test_owner_can_delete_promotion(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);
        $promotion = Promotion::factory()->create(['business_id' => $business->id]);

        $response = $this->actingAs($user)->delete(route('promotions.destroy', $promotion));

        $response->assertRedirect();
        $this->assertSoftDeleted('promotions', ['id' => $promotion->id]);
    }
}

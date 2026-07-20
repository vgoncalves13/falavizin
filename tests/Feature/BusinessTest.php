<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Livewire\Business\BusinessForm;
use App\Models\Business;
use App\Models\Category;
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
        $response = $this->get(route('businesses.index'));

        $response->assertStatus(200);
    }

    public function test_business_show_is_accessible_to_guests(): void
    {
        $business = Business::factory()->create();

        $response = $this->get(route('businesses.show', $business));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_pending_business(): void
    {
        $business = Business::factory()->create(['status' => BusinessStatus::Pending]);

        $this->get(route('businesses.show', $business))->assertForbidden();
    }

    public function test_owner_and_admin_can_view_pending_business(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $business = Business::factory()->create([
            'user_id' => $owner->id,
            'status' => BusinessStatus::Pending,
        ]);

        $this->actingAs($owner)->get(route('businesses.show', $business))->assertOk();
        $this->actingAs($admin)->get(route('businesses.show', $business))->assertOk();
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
        $response = $this->get(route('businesses.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_create_business_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('businesses.create'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_business(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);

        $response = $this->actingAs($user)->post(route('businesses.store'), [
            'name' => 'Padaria do João',
            'category_id' => $category->id,
            'neighborhood' => 'Centro',
            'description' => 'A melhor padaria do bairro.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'name' => 'Padaria do João',
            'user_id' => $user->id,
            'status' => BusinessStatus::Pending->value,
            'claimed' => true,
        ]);
    }

    public function test_business_form_persists_opening_hours_on_create_and_update(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);

        Livewire::actingAs($user)
            ->test(BusinessForm::class)
            ->set('name', 'Mercado 24 Horas')
            ->set('categoryId', $category->id)
            ->set('neighborhood', 'Centro')
            ->set('openingHours.0.closed', false)
            ->set('openingHours.0.open', '08:00')
            ->set('openingHours.0.close', '18:00')
            ->call('save');

        $business = Business::where('name', 'Mercado 24 Horas')->firstOrFail();
        $this->assertSame('18:00', $business->opening_hours[0]['close']);

        Livewire::actingAs($user)
            ->test(BusinessForm::class, ['business' => $business])
            ->set('openingHours.0.close', '20:00')
            ->call('save');

        $this->assertSame('20:00', $business->fresh()->opening_hours[0]['close']);
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
        $response = $this->post(route('businesses.store'), [
            'name' => 'Padaria do João',
            'neighborhood' => 'Centro',
            'category_id' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_business_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('businesses.store'), []);

        $response->assertSessionHasErrors(['name', 'category_id', 'neighborhood']);
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

        Livewire::actingAs(User::factory()->create())
            ->test(BusinessForm::class, ['business' => $business])
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
            'category_id' => $category->id,
            'neighborhood' => 'Vila Nova',
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
            'category_id' => $category->id,
            'neighborhood' => 'Centro',
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

        $response = $this->actingAs($user)->post(route('promotions.store', $business), [
            'title' => '30% de desconto este final de semana',
            'description' => 'Promoção especial de feriado.',
        ]);

        $response->assertRedirect(route('businesses.show', $business));
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

        $response = $this->actingAs($user)->post(route('promotions.store', $business), [
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

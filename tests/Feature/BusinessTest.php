<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'status' => BusinessStatus::Approved->value,
            'claimed' => true,
        ]);
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

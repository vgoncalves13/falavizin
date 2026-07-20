<?php

namespace Tests\Feature\Promotion;

use App\Livewire\Business\PromotionForm;
use App\Models\Business;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeeklyLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_owner_cannot_create_promotion_through_livewire(): void
    {
        $business = Business::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(PromotionForm::class, ['business' => $business])
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_owner_cannot_edit_promotion_from_another_business(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $owner->id]);
        $otherPromotion = Promotion::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($owner)
            ->test(PromotionForm::class, ['business' => $business])
            ->call('startEdit', $otherPromotion->id);
    }

    public function test_business_can_create_first_promotion(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('promotions.store', $business), [
                'title' => 'Promoção inaugural',
                'description' => 'Primeira promoção do negócio.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('promotions', [
            'business_id' => $business->id,
            'title' => 'Promoção inaugural',
        ]);
    }

    public function test_business_cannot_create_second_promotion_within_7_days(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        Promotion::factory()->create([
            'business_id' => $business->id,
            'created_at' => now()->subDays(3),
        ]);

        $this->actingAs($user)
            ->post(route('promotions.store', $business), [
                'title' => 'Segunda promoção no prazo',
                'description' => 'Não deveria ser criada.',
            ])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('promotions', ['title' => 'Segunda promoção no prazo']);
    }

    public function test_business_can_create_promotion_after_7_days(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        Promotion::factory()->create([
            'business_id' => $business->id,
            'created_at' => now()->subDays(8),
        ]);

        $this->actingAs($user)
            ->post(route('promotions.store', $business), [
                'title' => 'Promoção após 7 dias',
                'description' => 'Deve ser criada normalmente.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('promotions', ['title' => 'Promoção após 7 dias']);
    }

    public function test_error_message_shows_next_available_date(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        Promotion::factory()->create([
            'business_id' => $business->id,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)
            ->post(route('promotions.store', $business), [
                'title' => 'Tentativa bloqueada',
            ]);

        $response->assertSessionHasErrors('title');

        $errors = session('errors')->get('title');
        $this->assertStringContainsString('Próxima disponível em', $errors[0]);
    }

    public function test_deleted_promotion_still_counts_for_cooldown(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $promotion = Promotion::factory()->create([
            'business_id' => $business->id,
            'created_at' => now()->subDays(1),
        ]);
        $promotion->delete();

        $this->actingAs($user)
            ->post(route('promotions.store', $business), [
                'title' => 'Tentativa após deleção',
            ])
            ->assertSessionHasErrors('title');
    }
}

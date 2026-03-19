<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_review(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Business\ReviewSection::class, ['business' => $business])
            ->set('rating', 5)
            ->set('body', 'Excelente serviço!')
            ->call('saveReview')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'business_id' => $business->id,
            'rating' => 5,
            'body' => 'Excelente serviço!',
        ]);
    }

    public function test_rating_is_required(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Business\ReviewSection::class, ['business' => $business])
            ->set('rating', 0)
            ->call('saveReview')
            ->assertHasErrors(['rating']);
    }

    public function test_user_can_update_existing_review(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        Review::create(['user_id' => $user->id, 'business_id' => $business->id, 'rating' => 3, 'body' => 'Ok']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Business\ReviewSection::class, ['business' => $business])
            ->set('rating', 5)
            ->set('body', 'Melhorou muito!')
            ->call('saveReview')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'business_id' => $business->id, 'rating' => 5]);
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_user_can_delete_own_review(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $review = Review::create(['user_id' => $user->id, 'business_id' => $business->id, 'rating' => 4]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Business\ReviewSection::class, ['business' => $business])
            ->call('deleteReview', $review->id);

        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $business = Business::factory()->create();
        $review = Review::create(['user_id' => $owner->id, 'business_id' => $business->id, 'rating' => 4]);

        Livewire::actingAs($other)
            ->test(\App\Livewire\Business\ReviewSection::class, ['business' => $business])
            ->call('deleteReview', $review->id)
            ->assertForbidden();
    }

    public function test_average_rating_is_shown_on_business_page(): void
    {
        $business = Business::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Review::create(['user_id' => $user1->id, 'business_id' => $business->id, 'rating' => 4]);
        Review::create(['user_id' => $user2->id, 'business_id' => $business->id, 'rating' => 2]);

        $this->get(route('businesses.show', $business))
            ->assertOk()
            ->assertSee('3,0');
    }
}

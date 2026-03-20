<?php

namespace Tests\Feature\Business;

use App\Livewire\Business\ReviewSection;
use App\Models\Business;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_reply_to_a_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $business = Business::factory()->claimed()->create(['user_id' => $owner->id]);
        $review = Review::create([
            'user_id' => $reviewer->id,
            'business_id' => $business->id,
            'rating' => 4,
            'body' => 'Ótimo atendimento!',
        ]);

        $this->actingAs($owner);

        Livewire::test(ReviewSection::class, ['business' => $business])
            ->call('startReply', $review->id)
            ->set('replyText', 'Obrigado pelo feedback!')
            ->call('saveReply');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'owner_reply' => 'Obrigado pelo feedback!',
        ]);

        $this->assertNotNull($review->fresh()->owner_replied_at);
    }

    public function test_owner_can_edit_an_existing_reply(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $business = Business::factory()->claimed()->create(['user_id' => $owner->id]);
        $review = Review::create([
            'user_id' => $reviewer->id,
            'business_id' => $business->id,
            'rating' => 3,
            'owner_reply' => 'Resposta original.',
            'owner_replied_at' => now(),
        ]);

        $this->actingAs($owner);

        Livewire::test(ReviewSection::class, ['business' => $business])
            ->call('startReply', $review->id)
            ->assertSet('replyText', 'Resposta original.')
            ->set('replyText', 'Resposta atualizada!')
            ->call('saveReply');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'owner_reply' => 'Resposta atualizada!',
        ]);
    }

    public function test_owner_can_delete_a_reply(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $business = Business::factory()->claimed()->create(['user_id' => $owner->id]);
        $review = Review::create([
            'user_id' => $reviewer->id,
            'business_id' => $business->id,
            'rating' => 2,
            'owner_reply' => 'Resposta aqui.',
            'owner_replied_at' => now(),
        ]);

        $this->actingAs($owner);

        Livewire::test(ReviewSection::class, ['business' => $business])
            ->call('deleteReply', $review->id);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'owner_reply' => null,
            'owner_replied_at' => null,
        ]);
    }

    public function test_non_owner_cannot_reply_to_review(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $reviewer = User::factory()->create();
        $business = Business::factory()->claimed()->create(['user_id' => $owner->id]);
        $review = Review::create([
            'user_id' => $reviewer->id,
            'business_id' => $business->id,
            'rating' => 5,
        ]);

        $this->actingAs($stranger);

        // saveReply also enforces authorization — should not persist any data
        Livewire::test(ReviewSection::class, ['business' => $business])
            ->set('replyText', 'Resposta não autorizada')
            ->call('saveReply');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'owner_reply' => null,
        ]);
    }

    public function test_reply_text_is_required(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $business = Business::factory()->claimed()->create(['user_id' => $owner->id]);
        $review = Review::create([
            'user_id' => $reviewer->id,
            'business_id' => $business->id,
            'rating' => 4,
        ]);

        $this->actingAs($owner);

        Livewire::test(ReviewSection::class, ['business' => $business])
            ->call('startReply', $review->id)
            ->set('replyText', '')
            ->call('saveReply')
            ->assertHasErrors(['replyText']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Notifications\ContentModerationNotification;
use App\Notifications\NewContentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_request_claim(): void
    {
        $business = Business::factory()->create(['claimed' => false, 'user_id' => null]);

        $this->post(route('neighborhood.businesses.claim.request', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_request_claim_for_admin_review(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $business = Business::factory()->create(['claimed' => false, 'user_id' => null]);

        $this->actingAs($user)
            ->post(route('neighborhood.businesses.claim.request', [
                ...$business->localNeighborhood->routeParameters(),
                'business' => $business,
            ]))
            ->assertRedirect(route('neighborhood.businesses.show', [
                ...$business->localNeighborhood->routeParameters(),
                'business' => $business,
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'claim_user_id' => $user->id,
            'claimed' => false,
        ]);
        $this->assertNotNull($business->fresh()->claim_requested_at);
        Notification::assertSentTo(
            $admin,
            NewContentNotification::class,
            fn (NewContentNotification $notification): bool => $notification->type === 'claim',
        );
    }

    public function test_pending_claim_cannot_be_replaced_by_another_user(): void
    {
        $requester = User::factory()->create();
        $otherUser = User::factory()->create();
        $business = Business::factory()->create([
            'claimed' => false,
            'user_id' => null,
            'claim_user_id' => $requester->id,
            'claim_requested_at' => now(),
        ]);

        $this->actingAs($otherUser)
            ->post(route('neighborhood.businesses.claim.request', [
                ...$business->localNeighborhood->routeParameters(),
                'business' => $business,
            ]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'claim_user_id' => $requester->id,
        ]);
    }

    public function test_already_claimed_business_cannot_be_claimed_again(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->claimed()->create();

        $this->actingAs($user)
            ->post(route('neighborhood.businesses.claim.request', [
                ...$business->localNeighborhood->routeParameters(),
                'business' => $business,
            ]))
            ->assertSessionHas('error');

        $this->assertNull($business->fresh()->claim_user_id);
    }

    public function test_admin_can_approve_claim(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $requester = User::factory()->create();
        $business = Business::factory()->create([
            'claimed' => false,
            'user_id' => null,
            'claim_user_id' => $requester->id,
            'claim_requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.claims.approve', $business))
            ->assertRedirect(route('admin.moderation.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'user_id' => $requester->id,
            'claimed' => true,
            'claim_user_id' => null,
            'claim_requested_at' => null,
        ]);
        $this->assertNotNull($business->fresh()->claimed_at);
        Notification::assertSentTo(
            $requester,
            ContentModerationNotification::class,
            fn (ContentModerationNotification $notification): bool => $notification->decision === 'approved',
        );
    }

    public function test_admin_can_reject_claim(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $requester = User::factory()->create();
        $business = Business::factory()->create([
            'claimed' => false,
            'user_id' => null,
            'claim_user_id' => $requester->id,
            'claim_requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.claims.reject', $business))
            ->assertRedirect(route('admin.moderation.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'user_id' => null,
            'claimed' => false,
            'claim_user_id' => null,
            'claim_requested_at' => null,
        ]);
        Notification::assertSentTo(
            $requester,
            ContentModerationNotification::class,
            fn (ContentModerationNotification $notification): bool => $notification->decision === 'rejected',
        );
    }

    public function test_non_admin_cannot_moderate_claim(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create();
        $business = Business::factory()->create([
            'claimed' => false,
            'user_id' => null,
            'claim_user_id' => $requester->id,
            'claim_requested_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.claims.approve', $business))
            ->assertForbidden();

        $this->assertFalse($business->fresh()->claimed);
    }

    public function test_admin_moderation_page_lists_pending_claim(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $requester = User::factory()->create();
        $business = Business::factory()->create([
            'claimed' => false,
            'user_id' => null,
            'claim_user_id' => $requester->id,
            'claim_requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.moderation.index'))
            ->assertOk()
            ->assertSee($business->name)
            ->assertSee($requester->email);
    }
}

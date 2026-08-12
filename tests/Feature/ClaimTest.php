<?php

namespace Tests\Feature;

use App\Actions\ApproveBusinessClaimAction;
use App\Enums\BusinessClaimStatus;
use App\Livewire\Business\BusinessClaimButton;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Models\BusinessManager;
use App\Models\User;
use App\Notifications\ClaimApprovedNotification;
use App\Notifications\ClaimRejectedNotification;
use App\Notifications\ClaimSubmittedNotification;
use App\Notifications\NewContentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    private function unclaimedBusiness(): Business
    {
        return Business::factory()->create(['claimed' => false, 'user_id' => null]);
    }

    public function test_guest_cannot_request_claim(): void
    {
        $business = $this->unclaimedBusiness();

        $this->post(route('neighborhood.businesses.claim.request', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))
            ->assertRedirect(route('login'));
    }

    public function test_guest_claim_button_redirects_to_login(): void
    {
        $business = $this->unclaimedBusiness();

        Livewire::test(BusinessClaimButton::class, ['business' => $business])
            ->call('start')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_request_claim(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $business = $this->unclaimedBusiness();

        Livewire::actingAs($user)
            ->test(BusinessClaimButton::class, ['business' => $business])
            ->call('start')
            ->set('confirm', true)
            ->set('message', 'Sou o proprietário')
            ->call('submit');

        $claim = BusinessClaim::query()
            ->where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->assertSame(BusinessClaimStatus::Pending, $claim->status);
        $this->assertSame('Sou o proprietário', $claim->message);
        $this->assertFalse($business->fresh()->claimed);

        Notification::assertSentTo(
            $admin,
            NewContentNotification::class,
            fn (NewContentNotification $notification): bool => $notification->type === 'claim',
        );
        Notification::assertSentTo($user, ClaimSubmittedNotification::class);
    }

    public function test_claim_via_http_route_requires_confirmation(): void
    {
        $user = User::factory()->create();
        $business = $this->unclaimedBusiness();

        $this->actingAs($user)
            ->post(route('neighborhood.businesses.claim.request', [
                ...$business->localNeighborhood->routeParameters(),
                'business' => $business,
            ]))
            ->assertSessionHasErrors('confirm');

        $this->assertDatabaseMissing('business_claims', ['business_id' => $business->id]);
    }

    public function test_duplicate_pending_claim_is_blocked(): void
    {
        $user = User::factory()->create();
        $business = $this->unclaimedBusiness();

        BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(BusinessClaimButton::class, ['business' => $business])
            ->call('start')
            ->set('confirm', true)
            ->call('submit');

        $this->assertSame(1, BusinessClaim::where('business_id', $business->id)->where('user_id', $user->id)->count());
    }

    public function test_pending_claim_blocks_other_users(): void
    {
        $requester = User::factory()->create();
        $other = User::factory()->create();
        $business = $this->unclaimedBusiness();

        BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $requester->id]);

        Livewire::actingAs($other)
            ->test(BusinessClaimButton::class, ['business' => $business])
            ->call('start')
            ->set('confirm', true)
            ->call('submit');

        $this->assertNull(BusinessClaim::where('business_id', $business->id)->where('user_id', $other->id)->first());
    }

    public function test_non_admin_cannot_approve_claim(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create();
        $business = $this->unclaimedBusiness();
        $claim = BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $requester->id]);

        $this->actingAs($user)
            ->post(route('admin.claims.approve', $claim))
            ->assertForbidden();

        $this->assertSame(BusinessClaimStatus::Pending, $claim->fresh()->status);
        $this->assertFalse($business->fresh()->claimed);
    }

    public function test_admin_can_approve_claim_and_link_manager(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $requester = User::factory()->create();
        $business = $this->unclaimedBusiness();
        $claim = BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $requester->id]);

        $this->actingAs($admin)
            ->post(route('admin.claims.approve', $claim))
            ->assertRedirect(route('admin.claims.index'))
            ->assertSessionHas('success');

        $claim->refresh();
        $business->refresh();

        $this->assertSame(BusinessClaimStatus::Approved, $claim->status);
        $this->assertSame($admin->id, $claim->reviewed_by);
        $this->assertNotNull($claim->reviewed_at);
        $this->assertTrue($business->claimed);
        $this->assertSame($requester->id, $business->user_id);
        $this->assertTrue($business->isManagedBy($requester));
        $this->assertSame(1, BusinessManager::where('business_id', $business->id)->count());

        Notification::assertSentTo($requester, ClaimApprovedNotification::class);
        $this->assertDatabaseHas('moderation_logs', ['action' => 'claim_approved']);
    }

    public function test_repeated_approval_does_not_duplicate_manager(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $requester = User::factory()->create();
        $business = $this->unclaimedBusiness();
        $claim = BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $requester->id]);

        $action = new ApproveBusinessClaimAction;

        $action->execute($claim, $admin);
        $action->execute($claim->fresh(), $admin);

        $this->assertSame(1, BusinessManager::where('business_id', $business->id)->where('user_id', $requester->id)->count());
        $this->assertSame(1, $business->managers()->count());
    }

    public function test_rejection_creates_no_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $requester = User::factory()->create();
        $business = $this->unclaimedBusiness();
        $claim = BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $requester->id]);

        $this->actingAs($admin)
            ->post(route('admin.claims.reject', $claim), ['reason' => 'Documentação insuficiente'])
            ->assertRedirect(route('admin.claims.index'));

        $claim->refresh();

        $this->assertSame(BusinessClaimStatus::Rejected, $claim->status);
        $this->assertSame('Documentação insuficiente', $claim->rejection_reason);
        $this->assertFalse($business->fresh()->claimed);
        $this->assertDatabaseMissing('business_managers', ['business_id' => $business->id]);

        Notification::assertSentTo(
            $requester,
            ClaimRejectedNotification::class,
            fn (ClaimRejectedNotification $notification): bool => $notification->reason === 'Documentação insuficiente',
        );
    }

    public function test_rejected_claim_allows_new_request(): void
    {
        $user = User::factory()->create();
        $business = $this->unclaimedBusiness();

        $rejected = BusinessClaim::factory()->rejected()->create(['business_id' => $business->id, 'user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(BusinessClaimButton::class, ['business' => $business])
            ->call('start')
            ->set('confirm', true)
            ->call('submit');

        $this->assertDatabaseHas('business_claims', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'status' => BusinessClaimStatus::Pending->value,
        ]);
        $this->assertSame(BusinessClaimStatus::Rejected, $rejected->fresh()->status);
    }

    public function test_approved_manager_can_manage_business(): void
    {
        $owner = User::factory()->create();
        $business = $this->unclaimedBusiness();
        $claim = BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $owner->id]);

        (new ApproveBusinessClaimAction)->execute($claim, User::factory()->create(['is_admin' => true]));

        $this->assertTrue($owner->can('update', $business->fresh()));
    }

    public function test_non_manager_cannot_manage_business(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $business = $this->unclaimedBusiness();
        $claim = BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $owner->id]);

        (new ApproveBusinessClaimAction)->execute($claim, User::factory()->create(['is_admin' => true]));

        $this->assertFalse($stranger->can('update', $business->fresh()));
    }

    public function test_already_claimed_business_cannot_be_claimed_again(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->claimed()->create(['user_id' => $owner->id]);

        BusinessManager::create([
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'granted_at' => now(),
        ]);

        $other = User::factory()->create();

        Livewire::actingAs($other)
            ->test(BusinessClaimButton::class, ['business' => $business])
            ->call('start')
            ->set('confirm', true)
            ->call('submit');

        $this->assertDatabaseMissing('business_claims', ['business_id' => $business->id, 'user_id' => $other->id]);
    }

    public function test_admin_claims_page_lists_and_filters_claims(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $requester = User::factory()->create();
        $business = $this->unclaimedBusiness();
        BusinessClaim::factory()->create(['business_id' => $business->id, 'user_id' => $requester->id, 'message' => 'Sou dono']);

        $this->actingAs($admin)
            ->get(route('admin.claims.index'))
            ->assertOk()
            ->assertSee($business->name)
            ->assertSee($requester->email);

        $this->actingAs($admin)
            ->get(route('admin.claims.index', ['status' => 'approved']))
            ->assertOk()
            ->assertDontSee($business->name);
    }
}

<?php

namespace Tests\Feature;

use App\Mail\ClaimBusinessMail;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_request_claim(): void
    {
        $business = Business::factory()->create(['claimed' => false]);

        $response = $this->post(route('businesses.claim.request', $business));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_request_claim(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $business = Business::factory()->create(['claimed' => false]);

        $response = $this->actingAs($user)->post(route('businesses.claim.request', $business));

        $response->assertRedirect(route('businesses.show', $business));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('businesses', [
            'id' => $business->id,
            'claim_token' => null,
        ]);

        Mail::assertSent(ClaimBusinessMail::class, function (ClaimBusinessMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_already_claimed_business_cannot_be_claimed_again(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $business = Business::factory()->claimed()->create();

        $response = $this->actingAs($user)->post(route('businesses.claim.request', $business));

        $response->assertRedirect(route('businesses.show', $business));
        $response->assertSessionHas('error');

        Mail::assertNothingSent();
    }

    public function test_guest_cannot_verify_claim(): void
    {
        $business = Business::factory()->create(['claim_token' => 'some-token']);

        $response = $this->get(route('businesses.claim.verify', 'some-token'));

        $response->assertRedirect(route('login'));
    }

    public function test_valid_token_claims_business(): void
    {
        $user = User::factory()->create();
        $token = 'valid-claim-token-uuid';
        $business = Business::factory()->create([
            'claimed' => false,
            'claim_token' => $token,
            'user_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('businesses.claim.verify', $token));

        $response->assertRedirect(route('businesses.show', $business));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'user_id' => $user->id,
            'claimed' => true,
            'claim_token' => null,
        ]);

        $this->assertNotNull($business->fresh()->claimed_at);
    }

    public function test_invalid_token_redirects_to_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('businesses.claim.verify', 'invalid-token'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }
}

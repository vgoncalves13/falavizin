<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'endpoint' => 'https://push.example.test/subscription-1',
            'keys' => [
                'p256dh' => 'public-key',
                'auth' => 'auth-token',
            ],
            'content_encoding' => 'aes128gcm',
        ], $overrides);
    }

    public function test_guest_cannot_create_a_push_subscription(): void
    {
        $this->postJson(route('push-subscriptions.store'), $this->payload())
            ->assertUnauthorized();
    }

    public function test_user_can_create_and_update_a_push_subscription(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $this->payload([
                'types' => ['comment', 'post_vote'],
            ]))
            ->assertOk()
            ->assertJson(['enabled' => true]);

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $this->payload([
                'keys' => ['auth' => 'new-auth-token'],
            ]))
            ->assertOk();

        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_type' => User::class,
            'subscribable_id' => $user->id,
            'endpoint' => 'https://push.example.test/subscription-1',
            'auth_token' => 'new-auth-token',
        ]);

        $preferences = $user->fresh()->notification_preferences;
        $this->assertTrue($preferences['push']['comment']);
        $this->assertTrue($preferences['push']['post_vote']);
        $this->assertFalse($preferences['push']['moderation']);
    }

    public function test_existing_subscription_is_reassigned_after_explicit_activation(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser)
            ->postJson(route('push-subscriptions.store'), $this->payload())
            ->assertOk();

        $this->actingAs($secondUser)
            ->postJson(route('push-subscriptions.store'), $this->payload())
            ->assertOk();

        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $secondUser->id,
            'endpoint' => 'https://push.example.test/subscription-1',
        ]);
    }

    public function test_user_can_remove_only_their_own_subscription(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->updatePushSubscription(
            'https://push.example.test/subscription-1',
            'public-key',
            'auth-token',
            'aes128gcm',
        );

        $this->actingAs($otherUser)
            ->deleteJson(route('push-subscriptions.destroy'), [
                'endpoint' => 'https://push.example.test/subscription-1',
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $owner->id,
            'endpoint' => 'https://push.example.test/subscription-1',
        ]);

        $this->actingAs($owner)
            ->deleteJson(route('push-subscriptions.destroy'), [
                'endpoint' => 'https://push.example.test/subscription-1',
            ])
            ->assertNoContent();

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://push.example.test/subscription-1',
        ]);
    }

    public function test_subscription_requires_https_and_valid_notification_types(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('push-subscriptions.store'), $this->payload([
                'endpoint' => 'http://push.example.test/subscription-1',
                'types' => ['unknown'],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['endpoint', 'types.0']);
    }
}

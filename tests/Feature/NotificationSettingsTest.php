<?php

namespace Tests\Feature;

use App\Livewire\Profile\NotificationSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_preferences_start_disabled_and_can_be_enabled_explicitly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(NotificationSettings::class)
            ->assertSet('preferences', [])
            ->call('togglePreference', 'push', 'comment')
            ->assertDispatched('preferences-saved');

        $this->assertTrue($user->fresh()->notification_preferences['push']['comment']);
    }

    public function test_updating_push_preserves_existing_email_preferences(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'moderation' => false,
                'new_content' => true,
            ],
        ]);

        $this->actingAs($user);

        Livewire::test(NotificationSettings::class)
            ->call('togglePreference', 'push', 'moderation');

        $preferences = $user->fresh()->notification_preferences;

        $this->assertFalse($preferences['moderation']);
        $this->assertTrue($preferences['new_content']);
        $this->assertTrue($preferences['push']['moderation']);
    }

    public function test_updating_email_preserves_push_preferences(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'moderation' => true,
                'push' => ['comment' => true],
            ],
        ]);

        $this->actingAs($user);

        Livewire::test(NotificationSettings::class)
            ->call('togglePreference', 'email', 'moderation');

        $preferences = $user->fresh()->notification_preferences;

        $this->assertFalse($preferences['moderation']);
        $this->assertTrue($preferences['push']['comment']);
    }
}

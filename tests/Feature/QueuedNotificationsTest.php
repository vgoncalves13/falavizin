<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewContentNotification;
use App\Notifications\QueuedResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class QueuedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_channel_stays_sync_while_mail_is_queued_after_commit(): void
    {
        config()->set('queue.default', 'database');
        Bus::fake();

        $notification = new NewContentNotification('post', 'Aviso');

        $this->assertSame(3, $notification->tries);
        $this->assertSame([60, 300], $notification->backoff);
        $this->assertSame(30, $notification->timeout);

        User::factory()->create()->notify($notification);

        Bus::assertDispatched(SendQueuedNotifications::class, 2);
        Bus::assertDispatched(SendQueuedNotifications::class, fn (SendQueuedNotifications $job): bool => $job->channels === ['database'] && $job->connection === 'sync' && $job->afterCommit === true);
        Bus::assertDispatched(SendQueuedNotifications::class, fn (SendQueuedNotifications $job): bool => $job->channels === ['mail'] && $job->connection === 'database' && $job->afterCommit === true);
    }

    public function test_password_reset_mail_is_queued_after_commit(): void
    {
        config()->set('queue.default', 'database');
        Bus::fake();

        User::factory()->create()->sendPasswordResetNotification('reset-token');

        Bus::assertDispatched(SendQueuedNotifications::class, function (SendQueuedNotifications $job): bool {
            return $job->notification instanceof QueuedResetPassword
                && $job->channels === ['mail']
                && $job->connection === 'database'
                && $job->afterCommit === true;
        });
    }
}

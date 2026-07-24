<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentNotification;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Minishlink\WebPush\MessageSentReport;
use NotificationChannels\WebPush\ReportHandler;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Tests\TestCase;

class WebPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_preferences_default_to_disabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['comment' => true],
        ]);

        $this->assertTrue($user->wantsEmailNotification('comment'));
        $this->assertFalse($user->wantsPushNotification('comment'));
    }

    public function test_push_channel_respects_preference_and_device_subscription(): void
    {
        $comment = Comment::factory()->create();
        $notification = new CommentNotification($comment);
        $recipient = User::factory()->create([
            'notification_preferences' => ['push' => ['comment' => true]],
        ]);

        $this->assertNotContains(WebPushChannel::class, $notification->via($recipient));

        $recipient->updatePushSubscription(
            'https://push.example.test/subscription-1',
            'public-key',
            'auth-token',
            'aes128gcm',
        );

        $this->assertContains(WebPushChannel::class, $notification->via($recipient));

        $recipient->update(['notification_preferences' => ['push' => ['comment' => false]]]);

        $this->assertNotContains(WebPushChannel::class, $notification->via($recipient->fresh()));
    }

    public function test_database_notification_remains_and_is_idempotent(): void
    {
        $recipient = User::factory()->create();
        $comment = Comment::factory()->create();
        $notification = new CommentNotification($comment);

        Notification::sendNow($recipient, $notification, ['database']);
        Notification::sendNow($recipient, $notification, ['database']);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notification_deliveries', [
            'user_id' => $recipient->id,
            'notification_type' => CommentNotification::class,
            'event_key' => "comment:{$comment->id}",
            'channel' => 'database',
        ]);
    }

    public function test_web_push_delivery_is_enqueued(): void
    {
        config()->set('queue.default', 'database');
        Bus::fake();

        $recipient = User::factory()->create([
            'notification_preferences' => ['push' => ['comment' => true]],
        ]);
        $recipient->updatePushSubscription(
            'https://push.example.test/subscription-1',
            'public-key',
            'auth-token',
            'aes128gcm',
        );

        $recipient->notify(new CommentNotification(Comment::factory()->create()));

        Bus::assertDispatched(SendQueuedNotifications::class, function (SendQueuedNotifications $job): bool {
            return $job->channels === [WebPushChannel::class]
                && $job->connection === 'database'
                && $job->afterCommit === true;
        });
    }

    public function test_push_payload_contains_only_internal_navigation_data(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $notification = new CommentNotification($comment);
        $notification->id = 'notification-id';

        $payload = $notification->toWebPush(User::factory()->create())->toArray();

        $this->assertSame('notification-id', $payload['tag']);
        $this->assertSame($post->canonicalUrl(absolute: false), $payload['data']['url']);
        $this->assertArrayNotHasKey('endpoint', $payload['data']);
    }

    public function test_expired_endpoint_is_removed_by_the_package_report_handler(): void
    {
        $user = User::factory()->create();
        $subscription = $user->updatePushSubscription(
            'https://push.example.test/expired',
            'public-key',
            'auth-token',
            'aes128gcm',
        );
        $report = new MessageSentReport(
            new Request('POST', $subscription->endpoint),
            new Response(410),
            false,
            'Gone',
        );

        (new ReportHandler(app(Dispatcher::class)))
            ->handleReport($report, $subscription, (new WebPushMessage)->title('Teste'));

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://push.example.test/expired',
        ]);
    }
}

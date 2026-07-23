<?php

namespace App\Notifications;

use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use NotificationChannels\WebPush\WebPushChannel;

trait IdempotentNotification
{
    abstract public function eventKey(): string;

    public function shouldSend(object $notifiable, string $channel): bool
    {
        if (! $notifiable instanceof User) {
            return true;
        }

        $delivery = NotificationDelivery::firstOrCreate([
            'user_id' => $notifiable->id,
            'notification_type' => static::class,
            'event_key' => $this->eventKey(),
            'channel' => $this->deliveryChannel($channel),
        ]);

        return $delivery->wasRecentlyCreated;
    }

    public function afterSending(object $notifiable, string $channel, mixed $response): void
    {
        if (! $notifiable instanceof User) {
            return;
        }

        $this->deliveryQuery($notifiable, $channel)
            ->update(['delivered_at' => now()]);
    }

    public function releaseDeliveryReservation(object $notifiable, string $channel): void
    {
        if (! $notifiable instanceof User) {
            return;
        }

        $this->deliveryQuery($notifiable, $channel)
            ->whereNull('delivered_at')
            ->delete();
    }

    private function deliveryQuery(User $notifiable, string $channel): Builder
    {
        return NotificationDelivery::query()->where([
            'user_id' => $notifiable->id,
            'notification_type' => static::class,
            'event_key' => $this->eventKey(),
            'channel' => $this->deliveryChannel($channel),
        ]);
    }

    private function deliveryChannel(string $channel): string
    {
        return match ($channel) {
            WebPushChannel::class => 'webpush',
            default => $channel,
        };
    }
}

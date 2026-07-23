<?php

namespace App\Actions;

use App\Models\User;
use NotificationChannels\WebPush\PushSubscription;

class UpdatePushSubscriptionAction
{
    public function execute(User $user, array $data): PushSubscription
    {
        $subscription = $user->updatePushSubscription(
            endpoint: $data['endpoint'],
            key: $data['keys']['p256dh'],
            token: $data['keys']['auth'],
            contentEncoding: $data['content_encoding'] ?? null,
        );

        if (array_key_exists('types', $data)) {
            $preferences = $user->notification_preferences ?? [];
            $preferences['push'] = collect(User::PUSH_NOTIFICATION_TYPES)
                ->mapWithKeys(fn (string $type): array => [
                    $type => in_array($type, $data['types'], true),
                ])
                ->all();

            $user->update(['notification_preferences' => $preferences]);
        }

        return $subscription;
    }
}

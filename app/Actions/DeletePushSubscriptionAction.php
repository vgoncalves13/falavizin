<?php

namespace App\Actions;

use App\Models\User;

class DeletePushSubscriptionAction
{
    public function execute(User $user, string $endpoint): void
    {
        $user->deletePushSubscription($endpoint);
    }
}

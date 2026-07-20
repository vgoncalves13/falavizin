<?php

namespace App\Notifications;

trait QueuesMailAfterCommit
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public int $timeout = 30;

    /** @return array<string, string> */
    public function viaConnections(): array
    {
        return [
            'database' => 'sync',
            'mail' => config('queue.default'),
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class QueuedResetPassword extends ResetPassword implements ShouldQueueAfterCommit
{
    use Queueable, QueuesMailAfterCommit;
}

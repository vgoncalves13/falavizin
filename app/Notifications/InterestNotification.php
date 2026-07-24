<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InterestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post $post,
        public readonly User $merchant,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'hand-raised',
            'color' => 'text-blue-600',
            'message' => $this->merchant->name.' manifestou interesse no seu pedido: '.$this->post->title,
            'url' => $this->post->canonicalUrl(),
        ];
    }
}

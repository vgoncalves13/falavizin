<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Post $post) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $categoryName = $this->post->serviceCategory?->name ?? 'serviço';

        return [
            'icon' => 'megaphone',
            'color' => 'text-blue-600',
            'message' => 'Novo pedido de '.$categoryName.': '.$this->post->title,
            'url' => $this->post->canonicalUrl(),
        ];
    }
}

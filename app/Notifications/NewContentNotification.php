<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeLabel = match ($this->type) {
            'post' => 'post',
            'business' => 'negócio',
            'promotion' => 'promoção',
            default => 'conteúdo',
        };

        return (new MailMessage)
            ->subject('Novo '.$typeLabel.' aguardando aprovação — Hub do Bairro')
            ->greeting('Olá, admin!')
            ->line("Um novo {$typeLabel} foi enviado e aguarda aprovação:")
            ->line("**{$this->title}**")
            ->action('Ver painel de moderação', route('admin.moderation.index'))
            ->line('Acesse o painel para aprovar ou rejeitar.');
    }
}

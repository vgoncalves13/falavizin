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
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $typeLabel = match ($this->type) {
            'post' => 'post',
            'business' => 'negócio',
            'promotion' => 'promoção',
            default => 'conteúdo',
        };

        return [
            'icon' => 'clock',
            'color' => 'text-amber-500',
            'message' => "Nova requisição de {$typeLabel} aguardando aprovação: \"{$this->title}\"",
            'url' => route('admin.moderation.index'),
        ];
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
            ->subject('Nova requisição '.$typeLabel.' aguardando aprovação — Hub do Bairro')
            ->greeting('Olá, admin!')
            ->line("Uma nova requisição de {$typeLabel} foi enviado e aguarda aprovação:")
            ->line("**{$this->title}**")
            ->action('Ver painel de moderação', route('admin.moderation.index'))
            ->line('Acesse o painel para aprovar ou rejeitar.');
    }
}

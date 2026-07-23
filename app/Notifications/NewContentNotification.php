<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContentNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable, QueuesMailAfterCommit;

    public function __construct(
        public string $type,
        public string $title,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('new_content')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        $typeLabel = match ($this->type) {
            'post' => 'post',
            'business' => 'negócio',
            'claim' => 'reivindicação de negócio',
            'promotion' => 'promoção',
            default => 'conteúdo',
        };

        return [
            'icon' => 'clock',
            'color' => 'text-amber-500',
            'message' => "Nova solicitação de {$typeLabel} aguardando aprovação: \"{$this->title}\"",
            'url' => route('admin.moderation.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeLabel = match ($this->type) {
            'post' => 'post',
            'business' => 'negócio',
            'claim' => 'reivindicação de negócio',
            'promotion' => 'promoção',
            default => 'conteúdo',
        };

        return (new MailMessage)
            ->subject('Nova solicitação de '.$typeLabel.' aguardando aprovação — FalaVizin')
            ->greeting('Olá, admin!')
            ->line("Uma nova solicitação de {$typeLabel} foi recebida e aguarda aprovação:")
            ->line("**{$this->title}**")
            ->action('Ver painel de moderação', route('admin.moderation.index'))
            ->line('Acesse o painel para aprovar ou rejeitar.');
    }
}

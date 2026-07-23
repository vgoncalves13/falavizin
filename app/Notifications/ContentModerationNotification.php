<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentModerationNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable, QueuesMailAfterCommit;

    public function __construct(
        public string $type,
        public string $title,
        public string $decision,
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('moderation')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $approved = $this->decision === 'approved';

        $typeLabel = match ($this->type) {
            'post' => 'post',
            'business' => 'negócio',
            'promotion' => 'promoção',
            default => 'conteúdo',
        };

        return [
            'icon' => $approved ? 'check-circle' : 'x-circle',
            'color' => $approved ? 'text-green-600' : 'text-red-500',
            'message' => 'Seu '.$typeLabel.' foi '.($approved ? 'aprovado' : 'rejeitado').': '.$this->title,
            'url' => $this->url,
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

        $approved = $this->decision === 'approved';

        $message = (new MailMessage)
            ->subject(($approved ? '✓ ' : '✗ ').ucfirst($typeLabel).' '.($approved ? 'aprovado' : 'rejeitado').' — FalaVizin')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line($approved
                ? "Sua requisição de {$typeLabel} foi **aprovada** e já está visível no FalaVizin:"
                : "Infelizmente, sua requisição de {$typeLabel} foi **rejeitada** pela nossa equipe de moderação:")
            ->line("**{$this->title}**");

        if ($approved && $this->url) {
            $message->action('Ver '.ucfirst($typeLabel), $this->url);
        }

        if (! $approved) {
            $message->line('Se tiver dúvidas, entre em contato com a administração.');
        }

        return $message->line('Obrigado por contribuir com o FalaVizin!');
    }
}

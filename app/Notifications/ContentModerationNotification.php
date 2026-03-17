<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentModerationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $decision,
        public ?string $url = null,
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

        $approved = $this->decision === 'approved';

        $message = (new MailMessage)
            ->subject(($approved ? '✓ ' : '✗ ').ucfirst($typeLabel).' '.($approved ? 'aprovado' : 'rejeitado').' — Hub do Bairro')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line($approved
                ? "Seu {$typeLabel} foi **aprovado** e já está visível no Hub do Bairro:"
                : "Infelizmente, seu {$typeLabel} foi **rejeitado** pela nossa equipe de moderação:")
            ->line("**{$this->title}**");

        if ($approved && $this->url) {
            $message->action('Ver '.ucfirst($typeLabel), $this->url);
        }

        if (! $approved) {
            $message->line('Se tiver dúvidas, entre em contato com a administração.');
        }

        return $message->line('Obrigado por contribuir com o Hub do Bairro!');
    }
}

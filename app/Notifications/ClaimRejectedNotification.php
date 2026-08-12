<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimRejectedNotification extends Notification implements ShouldQueueAfterCommit
{
    use IdempotentNotification, Queueable, QueuesMailAfterCommit;

    public function __construct(
        public Business $business,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('moderation')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        $message = "Não foi possível aprovar sua solicitação para administrar \"{$this->business->name}\".";

        if ($this->reason) {
            $message .= ' Motivo: '.$this->reason;
        }

        return [
            'icon' => 'x-circle',
            'color' => 'text-red-500',
            'message' => $message,
            'url' => $this->business->canonicalUrl(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Não foi possível aprovar sua solicitação — FalaVizin')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line("Sua solicitação para administrar **{$this->business->name}** não foi aprovada.");

        if ($this->reason) {
            $mail->line('**Motivo:** '.$this->reason);
        }

        return $mail->line('Se tiver dúvidas, entre em contato com a administração.');
    }

    public function eventKey(): string
    {
        return "claim:{$this->business->id}:rejected";
    }
}

<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimSubmittedNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable, QueuesMailAfterCommit;

    public function __construct(public Business $business) {}

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
        return [
            'icon' => 'clock',
            'color' => 'text-amber-500',
            'message' => "Solicitação enviada para \"{$this->business->name}\". Nossa equipe vai analisar.",
            'url' => $this->business->canonicalUrl(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitação de reivindicação enviada — FalaVizin')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line("Recebemos sua solicitação para administrar **{$this->business->name}**.")
            ->line('Nossa equipe analisará e você será avisado quando o acesso for aprovado.')
            ->action('Ver estabelecimento', $this->business->canonicalUrl());
    }
}

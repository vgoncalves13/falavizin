<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanUpgradeRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public Business $business) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'star',
            'color' => 'text-amber-500',
            'message' => "Solicitação de upgrade para Destaque: \"{$this->business->name}\"",
            'url' => route('admin.moderation.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitação de upgrade para Destaque — Hub do Bairro')
            ->greeting('Olá, admin!')
            ->line("O negócio **{$this->business->name}** solicitou upgrade para o plano Destaque.")
            ->action('Ver painel de moderação', route('admin.moderation.index'))
            ->line('Acesse o painel para aprovar ou dispensar a solicitação.');
    }
}

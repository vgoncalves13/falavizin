<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanUpgradeApprovedNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable, QueuesMailAfterCommit;

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
            'message' => "Parabéns! Seu negócio \"{$this->business->name}\" foi promovido ao plano Destaque.",
            'url' => route('businesses.show', $this->business),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu negócio agora é Destaque — Hub do Bairro')
            ->greeting('Parabéns, '.$notifiable->name.'!')
            ->line("Seu negócio **{$this->business->name}** foi promovido ao plano **Destaque**.")
            ->line('Com o plano Destaque você pode criar promoções ilimitadas e aparece em primeiro nos resultados de busca.')
            ->action('Ver seu negócio', route('businesses.show', $this->business))
            ->line('Obrigado por fazer parte do Hub do Bairro!');
    }
}

<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ClaimApprovedNotification extends Notification implements ShouldQueueAfterCommit
{
    use IdempotentNotification, Queueable, QueuesMailAfterCommit;

    public function __construct(public Business $business) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('moderation')) {
            $channels[] = 'mail';
        }

        if (
            $notifiable->wantsPushNotification('moderation')
            && $notifiable->pushSubscriptions()->exists()
        ) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'check-circle',
            'color' => 'text-green-600',
            'message' => "Seu estabelecimento \"{$this->business->name}\" foi reivindicado com sucesso. Complete o perfil agora.",
            'url' => route('businesses.onboarding', $this->business),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu estabelecimento foi reivindicado com sucesso — FalaVizin')
            ->greeting('Parabéns, '.$notifiable->name.'!')
            ->line('Seu estabelecimento foi reivindicado com sucesso.')
            ->line("Agora você pode confirmar os dados, adicionar fotos e completar o perfil de **{$this->business->name}**.")
            ->action('Completar perfil', route('businesses.onboarding', $this->business))
            ->line('Obrigado por fazer parte do FalaVizin!');
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Seu estabelecimento foi reivindicado')
            ->body('Complete o perfil de '.$this->business->name)
            ->icon('/assets/icons/icon-192.png')
            ->badge('/assets/icons/badge-96.png')
            ->tag($this->id)
            ->data(['url' => route('businesses.onboarding', $this->business, absolute: false)])
            ->options(['TTL' => 86400]);
    }

    public function eventKey(): string
    {
        return "claim:{$this->business->id}:approved";
    }
}

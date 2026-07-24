<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PlanUpgradeApprovedNotification extends Notification implements ShouldQueueAfterCommit
{
    use IdempotentNotification, Queueable, QueuesMailAfterCommit;

    public function __construct(public Business $business) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailNotification('plan_upgrade')) {
            $channels[] = 'mail';
        }

        if (
            $notifiable->wantsPushNotification('plan_upgrade')
            && $notifiable->pushSubscriptions()->exists()
        ) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'star',
            'color' => 'text-amber-500',
            'message' => "Parabéns! Seu negócio \"{$this->business->name}\" foi promovido ao plano Destaque.",
            'url' => $this->business->canonicalUrl(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu negócio agora é Destaque — FalaVizin')
            ->greeting('Parabéns, '.$notifiable->name.'!')
            ->line("Seu negócio **{$this->business->name}** foi promovido ao plano **Destaque**.")
            ->line('Com o plano Destaque você pode criar promoções ilimitadas e aparece em primeiro nos resultados de busca.')
            ->action('Ver seu negócio', $this->business->canonicalUrl())
            ->line('Obrigado por fazer parte do FalaVizin!');
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Seu negócio agora é Destaque')
            ->body($this->business->name)
            ->icon('/assets/icons/icon-192.png')
            ->badge('/assets/icons/badge-96.png')
            ->tag($this->id)
            ->data(['url' => $this->business->canonicalUrl(absolute: false)])
            ->options(['TTL' => 86400]);
    }

    public function eventKey(): string
    {
        return "business:{$this->business->id}:upgrade-approved";
    }
}

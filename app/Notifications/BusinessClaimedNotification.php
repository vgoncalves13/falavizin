<?php

namespace App\Notifications;

use App\Models\Business;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessClaimedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Business $business,
        public readonly User $claimedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'flag',
            'color' => 'text-amber-600',
            'message' => "{$this->claimedBy->name} reivindicou o negócio \"{$this->business->name}\"",
            'url' => route('businesses.show', $this->business),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Negócio reivindicado — {$this->business->name}")
            ->greeting('Olá, admin!')
            ->line("{$this->claimedBy->name} ({$this->claimedBy->email}) reivindicou o negócio:")
            ->line("**{$this->business->name}**")
            ->action('Ver negócio', route('businesses.show', $this->business))
            ->line('Verifique se a reivindicação é legítima.');
    }
}

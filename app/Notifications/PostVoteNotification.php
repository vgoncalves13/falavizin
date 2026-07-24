<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PostVoteNotification extends Notification implements ShouldQueueAfterCommit
{
    use IdempotentNotification, Queueable, QueuesMailAfterCommit;

    public function __construct(
        public readonly Post $post,
        public readonly User $voter,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (
            $notifiable->wantsPushNotification('post_vote')
            && $notifiable->pushSubscriptions()->exists()
        ) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'hand-thumb-up',
            'color' => 'text-amber-500',
            'message' => $this->voter->name.' reagiu à sua publicação: '.$this->post->title,
            'url' => $this->post->canonicalUrl(),
        ];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->voter->name.' reagiu à sua publicação')
            ->body($this->post->title)
            ->icon('/assets/icons/icon-192.png')
            ->badge('/assets/icons/badge-96.png')
            ->tag($this->id)
            ->data(['url' => $this->post->canonicalUrl(absolute: false)])
            ->options(['TTL' => 3600]);
    }

    public function eventKey(): string
    {
        return "post:{$this->post->id}:voter:{$this->voter->id}";
    }
}

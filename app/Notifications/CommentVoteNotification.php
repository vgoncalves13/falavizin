<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CommentVoteNotification extends Notification implements ShouldQueueAfterCommit
{
    use IdempotentNotification, Queueable, QueuesMailAfterCommit;

    public function __construct(
        public readonly Comment $comment,
        public readonly User $voter,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (
            $notifiable->wantsPushNotification('comment_vote')
            && $notifiable->pushSubscriptions()->exists()
        ) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $preview = Str::limit($this->comment->body, 60);

        return [
            'icon' => 'hand-thumb-up',
            'color' => 'text-amber-500',
            'message' => $this->voter->name.' curtiu seu comentário: "'.$preview.'"',
            'url' => $this->comment->post->canonicalUrl(),
        ];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->voter->name.' reagiu ao seu comentário')
            ->body($this->comment->post->title)
            ->icon('/assets/icons/icon-192.png')
            ->badge('/assets/icons/badge-96.png')
            ->tag($this->id)
            ->data(['url' => $this->comment->post->canonicalUrl(absolute: false)])
            ->options(['TTL' => 3600]);
    }

    public function eventKey(): string
    {
        return "comment:{$this->comment->id}:voter:{$this->voter->id}";
    }
}

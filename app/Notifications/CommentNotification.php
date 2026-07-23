<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CommentNotification extends Notification implements ShouldQueueAfterCommit
{
    use IdempotentNotification, Queueable, QueuesMailAfterCommit;

    public function __construct(public readonly Comment $comment) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (
            $notifiable->wantsPushNotification('comment')
            && $notifiable->pushSubscriptions()->exists()
        ) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $isReply = $this->comment->parent_id !== null;
        $message = $isReply
            ? $this->comment->user->name.' respondeu ao seu comentário em: '.$this->comment->post->title
            : $this->comment->user->name.' comentou no seu post: '.$this->comment->post->title;

        return [
            'icon' => 'chat-bubble-left',
            'color' => 'text-amber-600',
            'message' => $message,
            'url' => route('feed.show', $this->comment->post),
        ];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $title = $this->comment->parent_id
            ? $this->comment->user->name.' respondeu ao seu comentário'
            : $this->comment->user->name.' comentou na sua publicação';

        return (new WebPushMessage)
            ->title($title)
            ->body($this->comment->post->title)
            ->icon('/assets/icons/icon-192.png')
            ->badge('/assets/icons/badge-96.png')
            ->tag($this->id)
            ->data(['url' => route('feed.show', $this->comment->post, absolute: false)])
            ->options(['TTL' => 3600]);
    }

    public function eventKey(): string
    {
        return "comment:{$this->comment->id}";
    }
}

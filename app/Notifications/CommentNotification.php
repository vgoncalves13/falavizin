<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Comment $comment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'chat-bubble-left',
            'color' => 'text-amber-600',
            'message' => $this->comment->user->name.' comentou no seu post: '.$this->comment->post->title,
            'url' => route('feed.show', $this->comment->post),
        ];
    }
}

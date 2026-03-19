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
}

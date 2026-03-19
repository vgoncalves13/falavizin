<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommentVoteNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Comment $comment,
        public readonly User $voter,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $preview = Str::limit($this->comment->body, 60);

        return [
            'icon' => 'hand-thumb-up',
            'color' => 'text-amber-500',
            'message' => $this->voter->name.' curtiu seu comentário: "'.$preview.'"',
            'url' => route('feed.show', $this->comment->post),
        ];
    }
}

<?php

namespace App\Enums;

enum PointEventReason: string
{
    case PostCreated = 'post_created';
    case CommentCreated = 'comment_created';
    case VoteReceived = 'vote_received';
    case BusinessClaimed = 'business_claimed';
    case PollCreated = 'poll_created';

    public function points(): int
    {
        return match ($this) {
            self::PostCreated => 5,
            self::CommentCreated => 2,
            self::VoteReceived => 3,
            self::BusinessClaimed => 10,
            self::PollCreated => 5,
        };
    }
}

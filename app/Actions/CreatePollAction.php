<?php

namespace App\Actions;

use App\Models\Poll;
use App\Models\Post;
use Carbon\Carbon;

class CreatePollAction
{
    public function execute(Post $post, string $question, array $options, ?Carbon $endsAt = null): Poll
    {
        $poll = $post->poll()->create([
            'question' => $question,
            'ends_at' => $endsAt,
        ]);

        foreach ($options as $text) {
            $poll->options()->create(['text' => $text]);
        }

        return $poll;
    }
}

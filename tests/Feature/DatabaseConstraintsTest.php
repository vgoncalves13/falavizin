<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Models\NotificationDelivery;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_post_cannot_have_more_than_one_poll(): void
    {
        $post = Post::factory()->create();
        Poll::factory()->create(['post_id' => $post->id]);

        $this->expectException(QueryException::class);

        Poll::factory()->create(['post_id' => $post->id]);
    }

    public function test_a_poll_vote_option_must_belong_to_the_same_poll(): void
    {
        $poll = Poll::factory()->create();
        $otherOption = PollOption::factory()->create();

        $this->expectException(QueryException::class);

        PollVote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $otherOption->id,
            'user_id' => User::factory()->create()->id,
        ]);
    }

    public function test_a_business_cannot_have_more_than_one_cover_photo(): void
    {
        $business = Business::factory()->create();
        BusinessPhoto::factory()->create([
            'business_id' => $business->id,
            'is_cover' => true,
        ]);

        $this->expectException(QueryException::class);

        BusinessPhoto::factory()->create([
            'business_id' => $business->id,
            'is_cover' => true,
        ]);
    }

    public function test_notification_delivery_is_unique_per_recipient_event_and_channel(): void
    {
        $attributes = [
            'user_id' => User::factory()->create()->id,
            'notification_type' => 'comment',
            'event_key' => 'comment:1',
            'channel' => 'database',
        ];

        NotificationDelivery::create($attributes);
        NotificationDelivery::create([...$attributes, 'channel' => 'webpush']);

        $this->expectException(QueryException::class);

        NotificationDelivery::create($attributes);
    }
}

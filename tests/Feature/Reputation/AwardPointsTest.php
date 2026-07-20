<?php

namespace Tests\Feature\Reputation;

use App\Actions\AwardPointsAction;
use App\Actions\CreatePostAction;
use App\Enums\PointEventReason;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_points_creates_point_event(): void
    {
        $user = User::factory()->create(['points' => 0]);

        (new AwardPointsAction)->execute($user, PointEventReason::PostCreated);

        $this->assertDatabaseHas('point_events', [
            'user_id' => $user->id,
            'points' => PointEventReason::PostCreated->points(),
            'reason' => PointEventReason::PostCreated->value,
        ]);
    }

    public function test_award_points_increments_user_points(): void
    {
        $user = User::factory()->create(['points' => 10]);

        (new AwardPointsAction)->execute($user, PointEventReason::CommentCreated);

        $this->assertEquals(10 + PointEventReason::CommentCreated->points(), $user->fresh()->points);
    }

    public function test_award_points_with_pointable_model(): void
    {
        $user = User::factory()->create(['points' => 0]);
        $post = Post::factory()->create(['user_id' => $user->id]);

        (new AwardPointsAction)->execute($user, PointEventReason::PostCreated, $post);

        $this->assertDatabaseHas('point_events', [
            'user_id' => $user->id,
            'reason' => PointEventReason::PostCreated->value,
            'pointable_type' => Post::class,
            'pointable_id' => $post->id,
        ]);
    }

    public function test_same_source_only_awards_points_once(): void
    {
        $user = User::factory()->create(['points' => 0]);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $action = new AwardPointsAction;

        $action->execute($user, PointEventReason::PostCreated, $post);
        $action->execute($user, PointEventReason::PostCreated, $post);

        $this->assertDatabaseCount('point_events', 1);
        $this->assertSame(PointEventReason::PostCreated->points(), $user->fresh()->points);
    }

    public function test_all_reason_points_values(): void
    {
        $this->assertEquals(5, PointEventReason::PostCreated->points());
        $this->assertEquals(2, PointEventReason::CommentCreated->points());
        $this->assertEquals(3, PointEventReason::VoteReceived->points());
        $this->assertEquals(10, PointEventReason::BusinessClaimed->points());
        $this->assertEquals(5, PointEventReason::PollCreated->points());
    }

    public function test_creating_post_awards_points(): void
    {
        $user = User::factory()->create(['points' => 0]);
        $category = Category::factory()->create(['type' => 'post']);

        (new CreatePostAction)->execute(
            user: $user,
            data: [
                'title' => 'Post de teste de pontos',
                'body' => 'Conteúdo do post aqui para teste.',
                'category_id' => $category->id,
            ],
        );

        $this->assertEquals(PointEventReason::PostCreated->points(), $user->fresh()->points);
    }
}

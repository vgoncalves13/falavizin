<?php

namespace Tests\Feature\Feed;

use App\Actions\CreatePostAction;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_be_created_with_event_dates(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['slug' => 'evento', 'type' => 'post']);

        $startsAt = Carbon::now()->addDays(3);
        $endsAt = Carbon::now()->addDays(3)->addHours(2);

        $post = (new CreatePostAction)->execute(
            user: $user,
            data: [
                'title' => 'Evento do bairro',
                'body' => 'Venha participar do nosso evento comunitário.',
                'category_id' => $category->id,
            ],
            eventStartsAt: $startsAt,
            eventEndsAt: $endsAt,
        );

        $this->assertDatabaseHas('posts', [
            'title' => 'Evento do bairro',
            'user_id' => $user->id,
        ]);

        $post->refresh();
        $this->assertNotNull($post->event_starts_at);
        $this->assertNotNull($post->event_ends_at);
        $this->assertEquals($startsAt->format('Y-m-d H:i'), $post->event_starts_at->format('Y-m-d H:i'));
    }

    public function test_upcoming_events_scope_returns_only_future_events(): void
    {
        $user = User::factory()->create();
        $eventCategory = Category::factory()->create(['slug' => 'evento', 'type' => 'post']);
        $otherCategory = Category::factory()->create(['slug' => 'aviso', 'type' => 'post']);

        // Past event - should NOT appear
        Post::factory()->create([
            'category_id' => $eventCategory->id,
            'user_id' => $user->id,
            'event_starts_at' => now()->subDay(),
        ]);

        // Future event - SHOULD appear
        $futureEvent = Post::factory()->create([
            'category_id' => $eventCategory->id,
            'user_id' => $user->id,
            'event_starts_at' => now()->addDays(2),
        ]);

        // Non-event post - should NOT appear
        Post::factory()->create([
            'category_id' => $otherCategory->id,
            'user_id' => $user->id,
        ]);

        $upcoming = Post::upcomingEvents()->get();

        $this->assertCount(1, $upcoming);
        $this->assertTrue($upcoming->first()->is($futureEvent));
    }

    public function test_event_without_dates_saves_null(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['slug' => 'evento', 'type' => 'post']);

        $post = (new CreatePostAction)->execute(
            user: $user,
            data: [
                'title' => 'Evento sem data definida',
                'body' => 'Evento sem datas preenchidas aqui.',
                'category_id' => $category->id,
            ],
        );

        $this->assertNull($post->event_starts_at);
        $this->assertNull($post->event_ends_at);
    }
}

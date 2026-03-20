<?php

namespace Tests\Feature\Feed;

use App\Actions\CreatePostAction;
use App\Enums\PointEventReason;
use App\Models\Category;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PollTest extends TestCase
{
    use RefreshDatabase;

    public function test_poll_can_be_created_with_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $post = (new CreatePostAction)->execute(
            user: $user,
            data: [
                'title' => 'Post com enquete',
                'body' => 'Conteúdo do post com enquete aqui.',
                'category_id' => $category->id,
            ],
            pollData: [
                'question' => 'Qual a sua opção favorita?',
                'options' => ['Opção A', 'Opção B', 'Opção C'],
                'ends_at' => null,
            ],
        );

        $this->assertDatabaseHas('polls', [
            'post_id' => $post->id,
            'question' => 'Qual a sua opção favorita?',
        ]);

        $this->assertDatabaseCount('poll_options', 3);
    }

    public function test_user_can_vote_on_poll(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $post = Post::factory()->create(['user_id' => $author->id, 'category_id' => $category->id]);
        $poll = Poll::create(['post_id' => $post->id, 'question' => 'Pergunta?']);
        $option = PollOption::create(['poll_id' => $poll->id, 'text' => 'Sim']);

        $this->actingAs($voter);

        $component = Livewire::test(\App\Livewire\Feed\PollVote::class, ['poll' => $poll])
            ->call('vote', $option->id);

        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $voter->id,
        ]);
    }

    public function test_user_cannot_vote_twice_on_same_poll(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $post = Post::factory()->create(['user_id' => $author->id, 'category_id' => $category->id]);
        $poll = Poll::create(['post_id' => $post->id, 'question' => 'Pergunta?']);
        $option = PollOption::create(['poll_id' => $poll->id, 'text' => 'Sim']);

        PollVote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $voter->id,
        ]);

        $this->actingAs($voter);

        $component = Livewire::test(\App\Livewire\Feed\PollVote::class, ['poll' => $poll])
            ->call('vote', $option->id);

        $this->assertDatabaseCount('poll_votes', 1);
    }

    public function test_ended_poll_cannot_receive_votes(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $post = Post::factory()->create(['user_id' => $author->id, 'category_id' => $category->id]);
        $poll = Poll::create([
            'post_id' => $post->id,
            'question' => 'Pergunta encerrada?',
            'ends_at' => now()->subDay(),
        ]);
        $option = PollOption::create(['poll_id' => $poll->id, 'text' => 'Sim']);

        $this->actingAs($voter);

        Livewire::test(\App\Livewire\Feed\PollVote::class, ['poll' => $poll])
            ->call('vote', $option->id);

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_voting_awards_points_to_post_author(): void
    {
        $author = User::factory()->create(['points' => 0]);
        $voter = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $post = Post::factory()->create(['user_id' => $author->id, 'category_id' => $category->id]);
        $poll = Poll::create(['post_id' => $post->id, 'question' => 'Pergunta?']);
        $option = PollOption::create(['poll_id' => $poll->id, 'text' => 'Sim']);

        $this->actingAs($voter);

        Livewire::test(\App\Livewire\Feed\PollVote::class, ['poll' => $poll])
            ->call('vote', $option->id);

        $this->assertEquals(
            PointEventReason::VoteReceived->points(),
            $author->fresh()->points
        );
    }
}

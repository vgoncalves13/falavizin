<?php

namespace Tests\Feature;

use App\Enums\VoteType;
use App\Livewire\Feed\VoteButtons;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_vote_helpful(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Livewire::actingAs($user)
            ->test(VoteButtons::class, ['post' => $post])
            ->call('vote', 'helpful')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'votable_type' => Post::class,
            'votable_id' => $post->id,
            'type' => VoteType::Helpful->value,
        ]);
    }

    public function test_authenticated_user_can_vote_not_helpful(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Livewire::actingAs($user)
            ->test(VoteButtons::class, ['post' => $post])
            ->call('vote', 'not_helpful')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'votable_type' => Post::class,
            'votable_id' => $post->id,
            'type' => VoteType::NotHelpful->value,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_voting(): void
    {
        $post = Post::factory()->create();

        Livewire::test(VoteButtons::class, ['post' => $post])
            ->call('vote', 'helpful')
            ->assertRedirect(route('login'));
    }

    public function test_voting_same_type_twice_removes_vote(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Vote::create([
            'user_id' => $user->id,
            'votable_type' => Post::class,
            'votable_id' => $post->id,
            'type' => VoteType::Helpful,
        ]);

        Livewire::actingAs($user)
            ->test(VoteButtons::class, ['post' => $post])
            ->call('vote', 'helpful')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('votes', [
            'user_id' => $user->id,
            'votable_type' => Post::class,
            'votable_id' => $post->id,
        ]);
    }

    public function test_voting_different_type_changes_vote(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Vote::create([
            'user_id' => $user->id,
            'votable_type' => Post::class,
            'votable_id' => $post->id,
            'type' => VoteType::Helpful,
        ]);

        Livewire::actingAs($user)
            ->test(VoteButtons::class, ['post' => $post])
            ->call('vote', 'not_helpful')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'votable_type' => Post::class,
            'votable_id' => $post->id,
            'type' => VoteType::NotHelpful->value,
        ]);

        $this->assertSame(
            1,
            Vote::where('user_id', $user->id)
                ->where('votable_type', Post::class)
                ->where('votable_id', $post->id)
                ->count()
        );
    }
}

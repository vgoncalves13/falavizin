<?php

namespace Tests\Feature\Feed;

use App\Livewire\Feed\SaveButton;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavePostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user);

        Livewire::test(SaveButton::class, ['post' => $post])
            ->call('toggle');

        $this->assertDatabaseHas('post_user_saves', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_unsave_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $post->saves()->attach($user->id);

        $this->actingAs($user);

        Livewire::test(SaveButton::class, ['post' => $post])
            ->call('toggle');

        $this->assertDatabaseMissing('post_user_saves', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_save_count_updates_after_toggle(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user);

        Livewire::test(SaveButton::class, ['post' => $post])
            ->assertSet('savesCount', 0)
            ->assertSet('saved', false)
            ->call('toggle')
            ->assertSet('savesCount', 1)
            ->assertSet('saved', true)
            ->call('toggle')
            ->assertSet('savesCount', 0)
            ->assertSet('saved', false);
    }

    public function test_guest_is_redirected_to_login_when_saving(): void
    {
        $post = Post::factory()->create();

        Livewire::test(SaveButton::class, ['post' => $post])
            ->call('toggle')
            ->assertRedirect(route('login'));
    }
}

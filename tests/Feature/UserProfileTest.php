<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\Business;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_is_accessible_to_guests(): void
    {
        $user = User::factory()->create();

        $this->get(route('users.show', $user))
            ->assertOk()
            ->assertSee($user->name);
    }

    public function test_profile_shows_approved_posts(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['status' => PostStatus::Approved]);
        Post::factory()->for($user)->create(['status' => PostStatus::Pending]);

        $this->get(route('users.show', $user))
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_profile_shows_approved_businesses(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create(['status' => BusinessStatus::Approved]);
        Business::factory()->for($user)->create(['status' => BusinessStatus::Pending]);

        $this->get(route('users.show', $user))
            ->assertOk()
            ->assertSee($business->name);
    }

    public function test_profile_shows_empty_state_when_no_content(): void
    {
        $user = User::factory()->create();

        $this->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('ainda não publicou nada');
    }

    public function test_profile_shows_neighborhood_when_set(): void
    {
        $user = User::factory()->create(['neighborhood' => 'Copacabana']);

        $this->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('Copacabana');
    }
}

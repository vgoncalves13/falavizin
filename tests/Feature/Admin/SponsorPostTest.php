<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sponsor_a_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create(['is_sponsored' => false]);

        $this->actingAs($admin)
            ->post(route('admin.posts.sponsor', $post))
            ->assertRedirect();

        $this->assertTrue($post->fresh()->is_sponsored);
    }

    public function test_admin_can_unsponsored_a_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create(['is_sponsored' => true]);

        $this->actingAs($admin)
            ->post(route('admin.posts.sponsor', $post))
            ->assertRedirect();

        $this->assertFalse($post->fresh()->is_sponsored);
    }

    public function test_non_admin_cannot_sponsor_post(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $post = Post::factory()->create(['is_sponsored' => false]);

        $this->actingAs($user)
            ->post(route('admin.posts.sponsor', $post))
            ->assertForbidden();

        $this->assertFalse($post->fresh()->is_sponsored);
    }

    public function test_guest_cannot_sponsor_post(): void
    {
        $post = Post::factory()->create(['is_sponsored' => false]);

        $this->post(route('admin.posts.sponsor', $post))
            ->assertRedirect(route('login'));
    }
}

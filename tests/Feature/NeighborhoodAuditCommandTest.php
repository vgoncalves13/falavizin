<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeighborhoodAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_passes_when_all_assignments_are_complete(): void
    {
        User::factory()->create();
        Post::factory()->create();
        Business::factory()->create();

        $this->artisan('neighborhoods:audit')
            ->assertSuccessful();
    }

    public function test_audit_command_fails_when_required_assignments_are_missing(): void
    {
        Post::factory()->create(['neighborhood_id' => null]);

        $this->artisan('neighborhoods:audit')
            ->expectsOutputToContain('posts sem bairro: 1')
            ->assertFailed();
    }

    public function test_audit_command_fails_when_businesses_are_orphaned(): void
    {
        Business::factory()->create(['neighborhood_id' => null]);

        $this->artisan('neighborhoods:audit')
            ->expectsOutputToContain('businesses sem bairro: 1')
            ->assertFailed();
    }

    public function test_audit_counts_users_with_null_neighborhood_but_still_passes(): void
    {
        User::factory()->count(3)->create(['neighborhood_id' => null]);
        Post::factory()->create();
        Business::factory()->create();

        $this->artisan('neighborhoods:audit')
            ->expectsOutputToContain('users sem bairro: 3')
            ->assertSuccessful();
    }

    public function test_audit_includes_trashed_records(): void
    {
        $post = Post::factory()->create(['neighborhood_id' => null]);
        $post->delete();

        $this->artisan('neighborhoods:audit')
            ->expectsOutputToContain('posts sem bairro: 1')
            ->assertFailed();
    }
}

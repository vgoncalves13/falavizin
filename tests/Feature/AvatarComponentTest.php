<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AvatarComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_component_displays_remote_and_local_images_or_initial(): void
    {
        $remoteUser = User::factory()->make([
            'name' => 'Ana',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);
        $localUser = User::factory()->make([
            'name' => 'Bruno',
            'avatar_url' => 'avatars/bruno.webp',
        ]);
        $userWithoutAvatar = User::factory()->make([
            'name' => 'Carlos',
            'avatar_url' => null,
        ]);

        $remote = Blade::render('<x-avatar :user="$user" />', ['user' => $remoteUser]);
        $local = Blade::render('<x-avatar :user="$user" />', ['user' => $localUser]);
        $fallback = Blade::render('<x-avatar :user="$user" />', ['user' => $userWithoutAvatar]);

        $this->assertStringContainsString('https://example.com/avatar.jpg', $remote);
        $this->assertStringContainsString('/storage/avatars/bruno.webp', $local);
        $this->assertStringContainsString('>C<', $fallback);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeCacheTest extends TestCase
{
    use DatabaseMigrations;

    public function test_domain_changes_invalidate_home_cache_after_commit(): void
    {
        Cache::clear();
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $postData = [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'status' => 'approved',
        ];
        $firstPost = Post::factory()->create($postData);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($firstPost->title);

        $secondPost = Post::factory()->create($postData);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($secondPost->title)
            ->assertViewHas('heroStats', fn (array $stats) => $stats['posts'] === 2);

        $view = file_get_contents(resource_path('views/home/index.blade.php'));

        $this->assertIsString($view);
        $this->assertStringNotContainsString('App\\Models', $view);
    }
}

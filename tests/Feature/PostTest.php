<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_is_accessible_to_guests(): void
    {
        $response = $this->get(route('feed.index'));

        $response->assertStatus(200);
    }

    public function test_post_show_is_accessible_to_guests(): void
    {
        $post = Post::factory()->create();

        $response = $this->get(route('feed.show', $post));

        $response->assertStatus(200);
    }

    public function test_create_post_page_requires_authentication(): void
    {
        $response = $this->get(route('feed.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_create_post_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('feed.create'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $response = $this->actingAs($user)->post(route('feed.store'), [
            'title' => 'Buraco na rua principal',
            'body' => 'Tem um buraco enorme na rua principal, precisa de atenção urgente.',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Buraco na rua principal',
            'user_id' => $user->id,
            'status' => PostStatus::Approved->value,
        ]);
    }

    public function test_post_store_requires_authentication(): void
    {
        $response = $this->post(route('feed.store'), [
            'title' => 'Teste',
            'body' => 'Conteúdo do post de teste aqui.',
            'category_id' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_post_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('feed.store'), []);

        $response->assertSessionHasErrors(['title', 'body', 'category_id']);
    }

    public function test_post_store_validates_category_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('feed.store'), [
            'title' => 'Título válido aqui',
            'body' => 'Conteúdo válido com mais de dez caracteres.',
            'category_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['category_id']);
    }

    public function test_author_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('feed.destroy', $post));

        $response->assertRedirect(route('feed.index'));
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_other_users_post(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->delete(route('feed.destroy', $post));

        $response->assertForbidden();
    }

    public function test_admin_can_delete_any_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create();

        $response = $this->actingAs($admin)->delete(route('feed.destroy', $post));

        $response->assertRedirect(route('feed.index'));
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }
}

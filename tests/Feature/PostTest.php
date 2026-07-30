<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Livewire\Feed\CreatePost;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_is_accessible_to_guests(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->get(route('neighborhood.feed.index', $neighborhood->routeParameters()));

        $response->assertStatus(200);
    }

    public function test_post_show_is_accessible_to_guests(): void
    {
        $post = Post::factory()->create();

        $response = $this->get(route('neighborhood.feed.show', [
            ...$post->neighborhood->routeParameters(),
            'post' => $post,
        ]));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_pending_post(): void
    {
        $post = Post::factory()->create(['status' => PostStatus::Pending]);

        $this->get(route('neighborhood.feed.show', [
            ...$post->neighborhood->routeParameters(),
            'post' => $post,
        ]))->assertForbidden();
    }

    public function test_author_and_admin_can_view_pending_post(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'status' => PostStatus::Pending,
        ]);

        $this->actingAs($author)->get(route('neighborhood.feed.show', [
            ...$post->neighborhood->routeParameters(),
            'post' => $post,
        ]))->assertOk();
        $this->actingAs($admin)->get(route('neighborhood.feed.show', [
            ...$post->neighborhood->routeParameters(),
            'post' => $post,
        ]))->assertOk();
    }

    public function test_create_post_page_requires_authentication(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->get(route('neighborhood.feed.create', $neighborhood->routeParameters()));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_create_post_page(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->create(['name' => 'Copacabana']);

        $response = $this->actingAs($user)->get(route('neighborhood.feed.create', $neighborhood->routeParameters()));

        $response->assertOk()->assertSee('Publicando em Copacabana');
    }

    public function test_legacy_create_post_page_uses_primary_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create(['name' => 'Tijuca']);
        $user = User::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->actingAs($user)
            ->get(route('feed.create'))
            ->assertOk()
            ->assertSee('Publicando em Tijuca');
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);
        $neighborhood = Neighborhood::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreatePost::class, ['neighborhood' => $neighborhood])
            ->set('title', 'Buraco na rua principal')
            ->set('body', 'Tem um buraco enorme na rua principal, precisa de atenção urgente.')
            ->set('categoryId', $category->id)
            ->call('save');

        $this->assertDatabaseHas('posts', [
            'title' => 'Buraco na rua principal',
            'user_id' => $user->id,
            'neighborhood_id' => $neighborhood->id,
            'status' => PostStatus::Approved->value,
        ]);
    }

    public function test_post_store_requires_authentication(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->get(route('neighborhood.feed.create', $neighborhood->routeParameters()));

        $response->assertRedirect(route('login'));
    }

    public function test_post_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreatePost::class, ['neighborhood' => $neighborhood])
            ->call('save')
            ->assertHasErrors(['title', 'body', 'categoryId']);
    }

    public function test_post_store_validates_category_exists(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreatePost::class, ['neighborhood' => $neighborhood])
            ->set('title', 'Título válido aqui')
            ->set('body', 'Conteúdo válido com mais de dez caracteres.')
            ->set('categoryId', 99999)
            ->call('save')
            ->assertHasErrors(['categoryId']);
    }

    public function test_author_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('feed.destroy', $post));

        $response->assertRedirect();
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

        $response->assertRedirect();
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }
}

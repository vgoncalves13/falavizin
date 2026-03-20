<?php

namespace Tests\Feature\Feed;

use App\Livewire\Feed\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_requires_authentication(): void
    {
        $post = Post::factory()->create();

        $this->get(route('feed.edit', $post))->assertRedirect(route('login'));
    }

    public function test_author_can_see_edit_page(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('feed.edit', $post))
            ->assertStatus(200);
    }

    public function test_non_author_cannot_see_edit_page(): void
    {
        $other = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($other)
            ->get(route('feed.edit', $post))
            ->assertForbidden();
    }

    public function test_author_can_update_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(EditPost::class, ['post' => $post])
            ->set('title', 'Título atualizado com sucesso')
            ->set('body', 'Conteúdo atualizado com muito mais detalhes aqui.')
            ->set('categoryId', $category->id)
            ->call('save');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Título atualizado com sucesso',
            'body' => 'Conteúdo atualizado com muito mais detalhes aqui.',
        ]);
    }

    public function test_component_is_prefilled_with_existing_data(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'location' => 'Rua das Flores']);

        $this->actingAs($user);

        Livewire::test(EditPost::class, ['post' => $post])
            ->assertSet('title', $post->title)
            ->assertSet('body', $post->body)
            ->assertSet('location', 'Rua das Flores')
            ->assertSet('categoryId', $post->category_id);
    }

    public function test_admin_can_edit_any_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create(['type' => 'post']);
        $post = Post::factory()->create();

        $this->actingAs($admin);

        Livewire::test(EditPost::class, ['post' => $post])
            ->set('title', 'Admin editou este post')
            ->set('body', 'Conteúdo editado pelo administrador do sistema.')
            ->set('categoryId', $category->id)
            ->call('save');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Admin editou este post',
        ]);
    }

    public function test_edit_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(EditPost::class, ['post' => $post])
            ->set('title', '')
            ->set('body', '')
            ->call('save')
            ->assertHasErrors(['title', 'body']);
    }
}

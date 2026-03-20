<?php

namespace Tests\Feature\Feed;

use App\Actions\CreatePostAction;
use App\Livewire\Feed\CreatePost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PostImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_be_created_without_image(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $post = (new CreatePostAction)->execute(
            user: $user,
            data: [
                'title' => 'Post sem imagem',
                'body' => 'Conteúdo do post sem imagem aqui.',
                'category_id' => $category->id,
            ],
        );

        $this->assertNull($post->image);
        $this->assertDatabaseHas('posts', ['title' => 'Post sem imagem', 'image' => null]);
    }

    public function test_post_image_field_is_nullable_in_database(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'image' => null,
        ]);

        $this->assertNull($post->fresh()->image);
    }

    public function test_post_with_image_stores_path(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        // Manually store the file as CreatePostAction would
        $path = Storage::disk('public')->put('posts', $file);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'image' => $path,
        ]);

        $this->assertNotNull($post->image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_image_validation_rejects_non_image_files(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        Livewire::actingAs($user)
            ->test(CreatePost::class)
            ->set('title', 'Post com arquivo inválido')
            ->set('body', 'Conteúdo do post aqui para teste.')
            ->set('categoryId', $category->id)
            ->set('image', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['image']);
    }
}

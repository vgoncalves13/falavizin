<?php

namespace Tests\Feature\Feed;

use App\Actions\CreatePostAction;
use App\Livewire\Feed\CreatePost;
use App\Models\Category;
use App\Models\Neighborhood;
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
        $neighborhood = Neighborhood::factory()->create();

        $post = (new CreatePostAction)->execute(
            user: $user,
            neighborhood: $neighborhood,
            data: [
                'title' => 'Post sem imagem',
                'body' => 'Conteúdo do post sem imagem aqui.',
                'category_id' => $category->id,
            ],
        );

        $this->assertNull($post->image);
        $this->assertSame([], $post->imagePaths());
        $this->assertDatabaseHas('posts', ['title' => 'Post sem imagem', 'image' => null, 'images' => null]);
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

    public function test_post_can_store_up_to_four_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);
        $neighborhood = Neighborhood::factory()->create();
        $files = collect(range(1, 4))
            ->map(fn (int $index) => UploadedFile::fake()->image("photo-{$index}.jpg", 800, 600))
            ->all();

        Livewire::actingAs($user)
            ->test(CreatePost::class, ['neighborhood' => $neighborhood])
            ->set('title', 'Post com quatro imagens')
            ->set('body', 'Conteúdo do post com quatro imagens.')
            ->set('categoryId', $category->id)
            ->set('images', $files)
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Post com quatro imagens')->firstOrFail();

        $this->assertCount(4, $post->imagePaths());
        $this->assertSame($post->imagePaths()[0], $post->image);

        foreach ($post->imagePaths() as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_post_rejects_more_than_four_images(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);
        $neighborhood = Neighborhood::factory()->create();
        $files = collect(range(1, 5))
            ->map(fn (int $index) => UploadedFile::fake()->image("photo-{$index}.jpg"))
            ->all();

        Livewire::actingAs($user)
            ->test(CreatePost::class, ['neighborhood' => $neighborhood])
            ->set('title', 'Post com imagens demais')
            ->set('body', 'Conteúdo válido para testar o limite.')
            ->set('categoryId', $category->id)
            ->set('images', $files)
            ->call('save')
            ->assertHasErrors(['images' => 'max']);
    }

    public function test_image_validation_rejects_non_image_files(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);
        $neighborhood = Neighborhood::factory()->create();

        Livewire::actingAs($user)
            ->test(CreatePost::class, ['neighborhood' => $neighborhood])
            ->set('title', 'Post com arquivo inválido')
            ->set('body', 'Conteúdo do post aqui para teste.')
            ->set('categoryId', $category->id)
            ->set('images', [UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')])
            ->call('save')
            ->assertHasErrors(['images.0']);
    }

    public function test_legacy_single_image_remains_visible(): void
    {
        $post = Post::factory()->create([
            'image' => 'posts/legacy.jpg',
            'images' => null,
        ]);

        $this->assertSame(['posts/legacy.jpg'], $post->imagePaths());
    }
}

<?php

namespace Tests\Feature;

use App\Actions\CreateBusinessAction;
use App\Actions\CreatePostAction;
use App\Livewire\Business\BusinessForm;
use App\Livewire\Feed\CreatePost;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class NeighborhoodPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_publishes_in_the_visited_neighborhood_not_the_primary_one(): void
    {
        $primary = Neighborhood::factory()->create();
        $visited = Neighborhood::factory()->create();
        $user = User::factory()->create(['neighborhood_id' => $primary->id]);
        $category = Category::factory()->create(['type' => 'post']);

        Livewire::actingAs($user)
            ->test(CreatePost::class, ['neighborhood' => $visited])
            ->set('title', 'Aviso importante no bairro')
            ->set('body', 'Conteúdo completo para os moradores.')
            ->set('categoryId', $category->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'neighborhood_id' => $visited->id,
        ]);
    }

    public function test_exact_duplicate_post_is_rejected_for_fifteen_minutes(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'neighborhood_id' => $neighborhood->id,
            'category_id' => $category->id,
            'title' => 'Mesmo título',
            'body' => 'Mesmo conteúdo',
            'created_at' => now()->subMinutes(5),
        ]);

        $this->expectException(ValidationException::class);

        app(CreatePostAction::class)->execute(
            $user,
            $neighborhood,
            ['category_id' => $post->category_id, 'title' => 'Mesmo título', 'body' => 'Mesmo conteúdo'],
        );
    }

    public function test_duplicate_post_is_allowed_after_fifteen_minutes(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Post::factory()->create([
            'user_id' => $user->id,
            'neighborhood_id' => $neighborhood->id,
            'category_id' => $category->id,
            'title' => 'Título repetido',
            'body' => 'Corpo repetido',
            'created_at' => now()->subMinutes(16),
        ]);

        $post = app(CreatePostAction::class)->execute(
            $user,
            $neighborhood,
            ['category_id' => $category->id, 'title' => 'Título repetido', 'body' => 'Corpo repetido'],
        );

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_five_posts_in_ten_minutes_are_rate_limited(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create();

        for ($i = 1; $i <= 5; $i++) {
            app(CreatePostAction::class)->execute(
                $user,
                $neighborhood,
                ['category_id' => $category->id, 'title' => "Post número {$i}", 'body' => "Conteúdo do post número {$i} aqui."],
            );
        }

        $this->expectException(ValidationException::class);

        app(CreatePostAction::class)->execute(
            $user,
            $neighborhood,
            ['category_id' => $category->id, 'title' => 'Post excedente', 'body' => 'Este post não deveria ser criado.'],
        );
    }

    public function test_three_businesses_on_same_day_are_rate_limited(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);

        for ($i = 1; $i <= 3; $i++) {
            app(CreateBusinessAction::class)->execute(
                $user,
                $neighborhood,
                [
                    'category_ids' => [$category->id],
                    'name' => "Negócio {$i}",
                    'neighborhood' => $neighborhood->name,
                    'city' => $neighborhood->city,
                ],
            );
        }

        $this->expectException(ValidationException::class);

        app(CreateBusinessAction::class)->execute(
            $user,
            $neighborhood,
            [
                'category_ids' => [$category->id],
                'name' => 'Negócio excedente',
                'neighborhood' => $neighborhood->name,
                'city' => $neighborhood->city,
            ],
        );
    }

    public function test_inactive_neighborhood_rejects_post_creation(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreatePostAction::class)->execute(
            $user,
            $neighborhood,
            ['category_id' => $category->id, 'title' => 'Post em bairro inativo', 'body' => 'Não deveria ser criado.'],
        );
    }

    public function test_inactive_neighborhood_rejects_business_creation(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);

        $this->expectException(ValidationException::class);

        app(CreateBusinessAction::class)->execute(
            $user,
            $neighborhood,
            [
                'category_ids' => [$category->id],
                'name' => 'Negócio em bairro inativo',
                'neighborhood' => $neighborhood->name,
                'city' => $neighborhood->city,
            ],
        );
    }

    public function test_business_form_creates_business_with_route_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);

        Livewire::actingAs($user)
            ->test(BusinessForm::class, ['neighborhood' => $neighborhood])
            ->set('name', 'Mercado do Bairro')
            ->set('categoryIds', [$category->id])
            ->set('whatsapp', '(21) 9 9999-9999')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('businesses', [
            'name' => 'Mercado do Bairro',
            'user_id' => $user->id,
            'neighborhood_id' => $neighborhood->id,
            'neighborhood' => $neighborhood->name,
            'city' => $neighborhood->city,
        ]);
    }

    public function test_post_action_stores_neighborhood_id(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $post = app(CreatePostAction::class)->execute(
            $user,
            $neighborhood,
            ['category_id' => $category->id, 'title' => 'Post com bairro', 'body' => 'Conteúdo do post com bairro associado.'],
        );

        $this->assertSame($neighborhood->id, $post->neighborhood_id);
    }

    public function test_business_action_stores_neighborhood_id_and_legacy_field(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);

        $business = app(CreateBusinessAction::class)->execute(
            $user,
            $neighborhood,
            [
                'category_ids' => [$category->id],
                'name' => 'Padaria Teste',
                'neighborhood' => $neighborhood->name,
                'city' => $neighborhood->city,
            ],
        );

        $this->assertSame($neighborhood->id, $business->neighborhood_id);
        $this->assertSame($neighborhood->name, $business->neighborhood);
        $this->assertSame($neighborhood->city, $business->city);
    }
}

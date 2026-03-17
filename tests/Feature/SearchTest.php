<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\Business;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_is_accessible_to_guests(): void
    {
        $response = $this->get(route('search.index'));

        $response->assertStatus(200);
    }

    public function test_search_page_shows_empty_state_without_query(): void
    {
        $response = $this->get(route('search.index'));

        $response->assertStatus(200);
        $response->assertSee('O que você está procurando?');
    }

    public function test_search_returns_matching_approved_posts(): void
    {
        $post = Post::factory()->create([
            'title' => 'Vazamento de água na Rua das Flores',
            'status' => PostStatus::Approved,
        ]);

        Post::factory()->create([
            'title' => 'Outro post sem relação',
            'status' => PostStatus::Approved,
        ]);

        $response = $this->get(route('search.index', ['q' => 'vazamento']));

        $response->assertStatus(200);
        $response->assertSee('Vazamento de água na Rua das Flores');
        $response->assertDontSee('Outro post sem relação');
    }

    public function test_search_does_not_return_pending_posts(): void
    {
        Post::factory()->create([
            'title' => 'Post pendente de aprovação',
            'status' => PostStatus::Pending,
        ]);

        $response = $this->get(route('search.index', ['q' => 'pendente']));

        $response->assertStatus(200);
        $response->assertDontSee('Post pendente de aprovação');
    }

    public function test_search_returns_matching_approved_businesses(): void
    {
        Business::factory()->create([
            'name' => 'Padaria São João',
            'status' => BusinessStatus::Approved,
        ]);

        Business::factory()->create([
            'name' => 'Açougue Central',
            'status' => BusinessStatus::Approved,
        ]);

        $response = $this->get(route('search.index', ['q' => 'padaria']));

        $response->assertStatus(200);
        $response->assertSee('Padaria São João');
        $response->assertDontSee('Açougue Central');
    }

    public function test_search_does_not_return_pending_businesses(): void
    {
        Business::factory()->create([
            'name' => 'Negócio Pendente',
            'status' => BusinessStatus::Pending,
        ]);

        $response = $this->get(route('search.index', ['q' => 'pendente']));

        $response->assertStatus(200);
        $response->assertDontSee('Negócio Pendente');
    }

    public function test_search_shows_no_results_message_when_nothing_found(): void
    {
        $response = $this->get(route('search.index', ['q' => 'termoquenaoexiste12345']));

        $response->assertStatus(200);
        $response->assertSee('Nenhum resultado para');
    }

    public function test_search_shows_total_results_count(): void
    {
        Post::factory()->create([
            'title' => 'Evento de música no parque',
            'status' => PostStatus::Approved,
        ]);

        $response = $this->get(route('search.index', ['q' => 'música']));

        $response->assertStatus(200);
        $response->assertSee('resultado');
    }
}

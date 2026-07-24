<?php

namespace Tests\Feature\Auth;

use App\Models\Neighborhood;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'neighborhood_id' => $neighborhood->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_registration_uses_the_validated_current_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->create();

        $this->withSession(['current_neighborhood_id' => $neighborhood->id])
            ->post(route('register'), [
                'name' => 'Novo morador',
                'email' => 'novo@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'neighborhood_id' => $neighborhood->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'novo@example.com',
            'neighborhood_id' => $neighborhood->id,
            'neighborhood' => $neighborhood->name,
        ]);
    }

    public function test_registration_requires_neighborhood_id(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('neighborhood_id');
        $this->assertGuest();
    }

    public function test_registration_rejects_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'neighborhood_id' => $neighborhood->id,
        ]);

        $response->assertSessionHasErrors('neighborhood_id');
        $this->assertGuest();
    }
}

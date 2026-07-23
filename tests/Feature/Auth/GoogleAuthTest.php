<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_sends_to_google(): void
    {
        $response = $this->get(route('auth.google.redirect'));

        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_google_callback_creates_new_user(): void
    {
        $socialiteUser = $this->createSocialiteUser();

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo@gmail.com',
            'name' => 'Novo Usuário',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'novo@gmail.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_google_callback_authenticates_existing_social_account(): void
    {
        $user = User::factory()->create(['email' => 'existente@gmail.com']);
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'existente@gmail.com',
        ]);

        $socialiteUser = $this->createSocialiteUser(
            id: 'google-123',
            email: 'existente@gmail.com',
            name: 'Existente',
        );

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_links_to_existing_user_with_same_email(): void
    {
        $user = User::factory()->create(['email' => 'mesmo@gmail.com']);

        $socialiteUser = $this->createSocialiteUser(
            email: 'mesmo@gmail.com',
            name: 'Outro Nome',
        );

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
        ]);
    }

    public function test_google_callback_does_not_duplicate_users(): void
    {
        $user = User::factory()->create(['email' => 'duplicate@gmail.com']);
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'duplicate@gmail.com',
        ]);

        $socialiteUser = $this->createSocialiteUser(
            id: 'google-123',
            email: 'duplicate@gmail.com',
        );

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $this->get(route('auth.google.callback'));

        $this->assertEquals(1, User::where('email', 'duplicate@gmail.com')->count());
        $this->assertEquals(1, SocialAccount::where('provider_user_id', 'google-123')->count());
    }

    public function test_google_callback_preserves_intended_url(): void
    {
        $socialiteUser = $this->createSocialiteUser();

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $response = $this->withSession(['url.intended' => '/feed/algum-post'])
            ->get(route('auth.google.callback'));

        $response->assertRedirect('/feed/algum-post');
    }

    public function test_google_callback_rejects_external_redirect(): void
    {
        $socialiteUser = $this->createSocialiteUser();

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $response = $this->withSession(['url.intended' => 'https://evil.com/steal'])
            ->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));
    }

    public function test_google_callback_does_not_overwrite_existing_avatar(): void
    {
        $user = User::factory()->create([
            'email' => 'avatar@gmail.com',
            'avatar_url' => 'https://example.com/my-avatar.jpg',
        ]);
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'avatar@gmail.com',
        ]);

        $socialiteUser = $this->createSocialiteUser(
            id: 'google-123',
            email: 'avatar@gmail.com',
            avatar: 'https://lh3.googleusercontent.com/new-avatar',
        );

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $this->get(route('auth.google.callback'));

        $user->refresh();
        $this->assertEquals('https://example.com/my-avatar.jpg', $user->avatar_url);
    }

    public function test_google_callback_fills_missing_avatar_for_linked_account(): void
    {
        $user = User::factory()->create([
            'email' => 'linked@gmail.com',
            'avatar_url' => null,
        ]);
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-linked',
            'provider_email' => 'linked@gmail.com',
        ]);

        Socialite::shouldReceive('driver->user')->andReturn($this->createSocialiteUser(
            id: 'google-linked',
            email: 'linked@gmail.com',
            avatar: 'https://lh3.googleusercontent.com/linked-avatar',
        ));

        $this->get(route('auth.google.callback'));

        $this->assertSame(
            'https://lh3.googleusercontent.com/linked-avatar',
            $user->refresh()->avatar_url,
        );
    }

    public function test_google_callback_handles_provider_error(): void
    {
        Socialite::shouldReceive('driver->user')
            ->andThrow(new \Exception('OAuth error'));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_google_callback_handles_missing_email(): void
    {
        $socialiteUser = $this->createSocialiteUser(email: null);

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_google_callback_handles_invalid_state(): void
    {
        Socialite::shouldReceive('driver->user')
            ->andThrow(new InvalidStateException);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_google_callback_handles_duplicate_provider_id(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@gmail.com']);
        SocialAccount::create([
            'user_id' => $user1->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'user1@gmail.com',
        ]);

        $socialiteUser = $this->createSocialiteUser(
            id: 'google-123',
            email: 'user2@gmail.com',
        );

        Socialite::shouldReceive('driver->user')
            ->andReturn($socialiteUser);

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($user1);
        $this->assertEquals(1, SocialAccount::where('provider_user_id', 'google-123')->count());
    }

    public function test_traditional_login_still_works(): void
    {
        User::factory()->create([
            'email' => 'trad@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'trad@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_traditional_register_still_works(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Novo User',
            'email' => 'novo@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'novo@test.com']);
    }

    private function createSocialiteUser(
        ?string $id = 'google-123',
        ?string $email = 'novo@gmail.com',
        ?string $name = 'Novo Usuário',
        ?string $avatar = 'https://lh3.googleusercontent.com/avatar',
    ): SocialiteUser {
        $user = new SocialiteUser;
        $user->map([
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
        ]);

        return $user;
    }
}

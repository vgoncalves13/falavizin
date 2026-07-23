<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_account_page_is_displayed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.account'))
            ->assertOk();
    }

    public function test_account_collections_are_paginated_independently(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(11)->for($user)->create();

        $this->actingAs($user)
            ->get(route('profile.account', ['tab' => 'posts', 'posts_page' => 2]))
            ->assertOk()
            ->assertViewHas('activeTab', 'posts')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 11
                && $posts->count() === 1
                && $posts->currentPage() === 2);
    }

    public function test_guest_cannot_access_account_page(): void
    {
        $this->get(route('profile.account'))
            ->assertRedirect(route('login'));
    }

    public function test_deleting_account_unclaims_owned_businesses(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'claimed' => true,
            'claimed_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $business->refresh();
        $this->assertNull($business->user_id);
        $this->assertFalse($business->claimed);
        $this->assertNull($business->claimed_at);
    }

    public function test_profile_update_saves_phone_and_neighborhood(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '(11) 99999-0000',
                'neighborhood' => 'Jardim Europa',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '(11) 99999-0000',
            'neighborhood' => 'Jardim Europa',
        ]);
    }

    public function test_user_can_upload_a_profile_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 800, 600),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $avatarPath = $user->refresh()->avatar_url;

        $this->assertNotNull($avatarPath);
        $this->assertStringStartsWith('avatars/', $avatarPath);
        Storage::disk('public')->assertExists($avatarPath);
    }

    public function test_profile_avatar_must_be_an_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->create('avatar.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('avatar');
    }

    public function test_replacing_an_avatar_removes_the_previous_local_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.webp', 'old');
        $user = User::factory()->create(['avatar_url' => 'avatars/old.webp']);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('new.png'),
            ])
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('avatars/old.webp');
        Storage::disk('public')->assertExists($user->refresh()->avatar_url);
    }
}

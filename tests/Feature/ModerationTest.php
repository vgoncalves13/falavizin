<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Livewire\Feed\CreatePost;
use App\Models\Business;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ContentModerationNotification;
use App\Notifications\NewContentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    // --- Fase 2: conteúdo criado fica pending ---

    public function test_new_post_is_created_as_pending(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $this->actingAs($user);

        Livewire::test(CreatePost::class)
            ->set('title', 'Buraco na rua principal')
            ->set('body', 'Tem um buraco enorme na rua principal.')
            ->set('categoryId', $category->id)
            ->call('save');

        $this->assertDatabaseHas('posts', [
            'title' => 'Buraco na rua principal',
            'status' => PostStatus::Pending->value,
        ]);
    }

    public function test_new_post_notifies_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'post']);

        $this->actingAs($user);

        Livewire::test(CreatePost::class)
            ->set('title', 'Evento no bairro')
            ->set('body', 'Grande evento comunitário no parque do bairro.')
            ->set('categoryId', $category->id)
            ->call('save');

        Notification::assertSentTo($admin, NewContentNotification::class);
    }

    public function test_new_business_is_created_as_pending(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);

        $this->actingAs($user)->post(route('businesses.store'), [
            'name' => 'Padaria do João',
            'category_id' => $category->id,
            'neighborhood' => 'Centro',
        ]);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Padaria do João',
            'status' => BusinessStatus::Pending->value,
        ]);
    }

    // --- Admin aprova/rejeita ---

    public function test_admin_can_approve_pending_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create(['status' => PostStatus::Pending]);

        $this->actingAs($admin)
            ->post(route('admin.moderation.approve', ['type' => 'post', 'id' => $post->id]))
            ->assertRedirect(route('admin.moderation.index'));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => PostStatus::Approved->value,
        ]);
        $this->assertNotNull($post->fresh()->approved_at);
    }

    public function test_admin_can_reject_pending_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create(['status' => PostStatus::Pending]);

        $this->actingAs($admin)
            ->post(route('admin.moderation.reject', ['type' => 'post', 'id' => $post->id]));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => PostStatus::Rejected->value,
        ]);
    }

    public function test_admin_can_approve_pending_business(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $business = Business::factory()->create(['status' => BusinessStatus::Pending]);

        $this->actingAs($admin)
            ->post(route('admin.moderation.approve', ['type' => 'business', 'id' => $business->id]));

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => BusinessStatus::Approved->value,
        ]);
    }

    public function test_author_is_notified_when_post_is_approved(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id, 'status' => PostStatus::Pending]);

        $this->actingAs($admin)
            ->post(route('admin.moderation.approve', ['type' => 'post', 'id' => $post->id]));

        Notification::assertSentTo($author, ContentModerationNotification::class, function ($n) {
            return $n->decision === 'approved' && $n->type === 'post';
        });
    }

    public function test_author_is_notified_when_post_is_rejected(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id, 'status' => PostStatus::Pending]);

        $this->actingAs($admin)
            ->post(route('admin.moderation.reject', ['type' => 'post', 'id' => $post->id]));

        Notification::assertSentTo($author, ContentModerationNotification::class, function ($n) {
            return $n->decision === 'rejected' && $n->type === 'post';
        });
    }

    public function test_author_is_notified_when_business_is_approved(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $owner->id, 'status' => BusinessStatus::Pending]);

        $this->actingAs($admin)
            ->post(route('admin.moderation.approve', ['type' => 'business', 'id' => $business->id]));

        Notification::assertSentTo($owner, ContentModerationNotification::class, function ($n) {
            return $n->decision === 'approved' && $n->type === 'business';
        });
    }

    // --- Reportar conteúdo ---

    public function test_authenticated_user_can_report_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post(route('report.post', $post), ['reason' => 'Conteúdo inapropriado ou ofensivo'])
            ->assertRedirect();

        $this->assertNotNull($post->fresh()->reported_at);
    }

    public function test_report_saves_the_reason(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post(route('report.post', $post), ['reason' => 'Spam ou publicidade indevida']);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'reported_reason' => 'Spam ou publicidade indevida',
        ]);
    }

    public function test_report_requires_a_reason(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post(route('report.post', $post), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->assertNull($post->fresh()->reported_at);
    }

    public function test_guest_cannot_report_a_post(): void
    {
        $post = Post::factory()->create();

        $this->post(route('report.post', $post), ['reason' => 'Spam ou publicidade indevida'])
            ->assertRedirect(route('login'));

        $this->assertNull($post->fresh()->reported_at);
    }

    public function test_authenticated_user_can_report_a_business(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();

        $this->actingAs($user)
            ->post(route('report.business', $business), ['reason' => 'Informação falsa ou enganosa'])
            ->assertRedirect();

        $this->assertNotNull($business->fresh()->reported_at);
    }

    public function test_reported_post_cleared_after_admin_action(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create([
            'status' => PostStatus::Approved,
            'reported_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.moderation.reject', ['type' => 'post', 'id' => $post->id]));

        $this->assertNull($post->fresh()->reported_at);
    }

    public function test_reporting_same_post_twice_shows_info(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['reported_at' => now()]);

        $this->actingAs($user)
            ->post(route('report.post', $post), ['reason' => 'Spam ou publicidade indevida'])
            ->assertRedirect()
            ->assertSessionHas('info');
    }
}

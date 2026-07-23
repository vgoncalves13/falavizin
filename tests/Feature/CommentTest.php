<?php

namespace Tests\Feature;

use App\Livewire\Feed\CommentSection;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->set('body', 'Este é um comentário válido.')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Este é um comentário válido.',
        ]);
    }

    public function test_comment_notifies_post_author_but_not_the_commenter(): void
    {
        Notification::fake();

        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        Livewire::actingAs($commenter)
            ->test(CommentSection::class, ['post' => $post])
            ->set('body', 'Comentário para o autor.')
            ->call('addComment');

        Notification::assertSentTo($author, CommentNotification::class);
        Notification::assertNotSentTo($commenter, CommentNotification::class);
    }

    public function test_reply_notifies_parent_author_but_not_the_replier(): void
    {
        Notification::fake();

        $commentAuthor = User::factory()->create();
        $replier = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $commentAuthor->id,
        ]);

        Livewire::actingAs($replier)
            ->test(CommentSection::class, ['post' => $post])
            ->set('replyingTo', $comment->id)
            ->set('replyBody', 'Resposta ao comentário.')
            ->call('addReply');

        Notification::assertSentTo($commentAuthor, CommentNotification::class);
        Notification::assertNotSentTo($replier, CommentNotification::class);
    }

    public function test_guest_cannot_add_comment(): void
    {
        $post = Post::factory()->create();

        Livewire::test(CommentSection::class, ['post' => $post])
            ->set('body', 'Tentando comentar como visitante.')
            ->call('addComment')
            ->assertRedirect(route('login'));
    }

    public function test_comment_body_is_required(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->set('body', '')
            ->call('addComment')
            ->assertHasErrors(['body' => 'required']);
    }

    public function test_comment_body_minimum_length(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->set('body', 'a')
            ->call('addComment')
            ->assertHasErrors(['body' => 'min']);
    }

    public function test_user_cannot_reply_to_comment_from_another_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $otherComment = Comment::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->set('replyingTo', $otherComment->id)
            ->set('replyBody', 'Resposta fora do post.')
            ->call('addReply');
    }

    public function test_owner_can_edit_their_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Comentário original.',
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->call('startEdit', $comment->id)
            ->assertSet('editingId', $comment->id)
            ->set('editBody', 'Comentário editado com sucesso.')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Comentário editado com sucesso.',
        ]);
    }

    public function test_user_cannot_edit_others_comment(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $other->id,
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->call('startEdit', $comment->id)
            ->assertForbidden();
    }

    public function test_owner_can_delete_their_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->call('deleteComment', $comment->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $other->id,
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->call('deleteComment', $comment->id)
            ->assertForbidden();
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        Livewire::actingAs($admin)
            ->test(CommentSection::class, ['post' => $post])
            ->call('deleteComment', $comment->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_comments_are_paginated(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(11)->create(['post_id' => $post->id, 'parent_id' => null]);

        Livewire::test(CommentSection::class, ['post' => $post])
            ->set('paginators.commentsPage', 2)
            ->assertViewHas('comments', fn ($comments) => $comments->total() === 11
                && $comments->count() === 1
                && $comments->currentPage() === 2);
    }
}

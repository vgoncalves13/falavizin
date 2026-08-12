<?php

namespace Tests\Feature;

use App\Actions\CreatePromotionAction;
use App\Livewire\Business\ReviewSection;
use App\Livewire\Feed\CommentSection;
use App\Livewire\Feed\InterestButton;
use App\Livewire\Feed\PollVote;
use App\Livewire\Feed\VoteButtons;
use App\Models\Business;
use App\Models\Comment;
use App\Models\Neighborhood;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class InactiveNeighborhoodTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->set('body', 'Não deve ser salvo')
            ->call('addComment')
            ->assertForbidden();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_reply_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->set('replyingTo', $comment->id)
            ->set('replyBody', 'Resposta bloqueada')
            ->call('addReply')
            ->assertForbidden();

        $this->assertDatabaseCount('comments', 1);
    }

    public function test_vote_on_post_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(VoteButtons::class, ['post' => $post])
            ->call('vote', 'helpful')
            ->assertForbidden();

        $this->assertDatabaseCount('votes', 0);
    }

    public function test_vote_on_comment_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->call('voteComment', $comment->id)
            ->assertForbidden();

        $this->assertDatabaseCount('votes', 0);
    }

    public function test_poll_vote_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $poll = Poll::factory()->create(['post_id' => $post->id]);
        $option = PollOption::factory()->create(['poll_id' => $poll->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PollVote::class, ['poll' => $poll])
            ->call('vote', $option->id)
            ->assertForbidden();

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_interest_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(InterestButton::class, ['post' => $post])
            ->call('toggle')
            ->assertForbidden();
    }

    public function test_review_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ReviewSection::class, ['business' => $business])
            ->set('rating', 5)
            ->set('body', 'Não deve ser salvo')
            ->call('saveReview')
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_deletion_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $user = User::factory()->create();
        $review = $business->reviews()->create(['user_id' => $user->id, 'rating' => 4]);

        Livewire::actingAs($user)
            ->test(ReviewSection::class, ['business' => $business])
            ->call('deleteReview', $review->id)
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'deleted_at' => null]);
    }

    public function test_promotion_creation_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->expectException(ValidationException::class);

        (new CreatePromotionAction)->execute($business, [
            'title' => 'Promoção inválida',
        ]);
    }

    public function test_claim_request_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create([
            'neighborhood_id' => $neighborhood->id,
            'claimed' => false,
            'user_id' => null,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('neighborhood.businesses.claim.request', [
                ...$business->localNeighborhood->routeParameters(),
                'business' => $business,
            ]), [
                'confirm' => '1',
            ])
            ->assertForbidden();

        $this->assertNull($business->fresh()->claim_user_id);
    }

    public function test_comment_edit_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->call('startEdit', $comment->id)
            ->assertForbidden();
    }

    public function test_comment_deletion_is_denied_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['post' => $post])
            ->call('deleteComment', $comment->id)
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
    }

    public function test_post_edit_is_denied_for_regular_user_in_inactive_neighborhood(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create([
            'neighborhood_id' => $neighborhood->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('neighborhood.feed.edit', [
                ...$post->neighborhood->routeParameters(),
                'post' => $post,
            ]))
            ->assertForbidden();
    }

    public function test_post_deletion_is_denied_for_regular_user_in_inactive_neighborhood(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create([
            'neighborhood_id' => $neighborhood->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('neighborhood.feed.destroy', [
                ...$post->neighborhood->routeParameters(),
                'post' => $post,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'deleted_at' => null]);
    }

    public function test_admin_can_still_edit_post_in_inactive_neighborhood(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->actingAs($admin)
            ->get(route('neighborhood.feed.edit', [
                ...$post->neighborhood->routeParameters(),
                'post' => $post,
            ]))
            ->assertOk();
    }

    public function test_admin_can_still_delete_post_in_inactive_neighborhood(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->actingAs($admin)
            ->delete(route('neighborhood.feed.destroy', [
                ...$post->neighborhood->routeParameters(),
                'post' => $post,
            ]))
            ->assertRedirect();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_inactive_neighborhood_banner_is_shown_on_feed_show(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('neighborhood.feed.show', [
            ...$post->neighborhood->routeParameters(),
            'post' => $post,
        ]))
            ->assertOk()
            ->assertSee('Este bairro não está mais ativo')
            ->assertSee('noindex');
    }

    public function test_inactive_neighborhood_banner_is_shown_on_business_show(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))
            ->assertOk()
            ->assertSee('Este bairro não está mais ativo')
            ->assertSee('noindex');
    }

    public function test_detail_pages_are_accessible_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('neighborhood.feed.show', [
            ...$post->neighborhood->routeParameters(),
            'post' => $post,
        ]))
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_business_detail_is_accessible_for_inactive_neighborhood(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))
            ->assertOk()
            ->assertSee($business->name);
    }

    public function test_community_controls_are_hidden_on_feed_show_for_inactive(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('neighborhood.feed.show', [
            ...$post->neighborhood->routeParameters(),
            'post' => $post,
        ]))
            ->assertOk()
            ->assertDontSee('Comentar')
            ->assertDontSee('Útil');
    }

    public function test_community_controls_are_hidden_on_business_show_for_inactive(): void
    {
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->get(route('neighborhood.businesses.show', [
            ...$business->localNeighborhood->routeParameters(),
            'business' => $business,
        ]))
            ->assertOk()
            ->assertDontSee('Avaliar este negócio')
            ->assertDontSee('Reivindicar');
    }

    public function test_report_still_visible_for_inactive_neighborhood(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $post = Post::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->actingAs($user)
            ->get(route('neighborhood.feed.show', [
                ...$post->neighborhood->routeParameters(),
                'post' => $post,
            ]))
            ->assertOk()
            ->assertSee('Reportar');
    }

    public function test_favorite_still_visible_for_inactive_business(): void
    {
        $user = User::factory()->create();
        $neighborhood = Neighborhood::factory()->inactive()->create();
        $business = Business::factory()->create(['neighborhood_id' => $neighborhood->id]);

        $this->actingAs($user)
            ->get(route('neighborhood.businesses.show', [
                ...$business->localNeighborhood->routeParameters(),
                'business' => $business,
            ]))
            ->assertOk()
            ->assertSee('Favoritar');
    }
}

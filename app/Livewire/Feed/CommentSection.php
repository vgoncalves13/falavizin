<?php

namespace App\Livewire\Feed;

use App\Actions\AwardPointsAction;
use App\Enums\PointEventReason;
use App\Enums\VoteType;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Vote;
use App\Notifications\CommentNotification;
use App\Notifications\CommentVoteNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CommentSection extends Component
{
    use WithPagination;

    public Post $post;

    public string $body = '';

    public ?int $editingId = null;

    public string $editBody = '';

    public ?int $replyingTo = null;

    public string $replyBody = '';

    protected function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'body.required' => 'Escreva algo antes de comentar.',
            'body.min' => 'O comentário deve ter pelo menos 3 caracteres.',
        ];
    }

    public function addComment(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $key = 'add-comment:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 15)) {
            $this->addError('body', 'Você está comentando rápido demais. Aguarde um momento.');

            return;
        }

        $this->validate();

        RateLimiter::hit($key, 3600);

        $comment = $this->post->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->body,
            'status' => 'approved',
        ]);

        // Notify the post author — but not if they commented on their own post
        $postAuthor = $this->post->user;
        if ($postAuthor && $postAuthor->id !== auth()->id()) {
            $postAuthor->notify(new CommentNotification($comment));
        }

        (new AwardPointsAction)->execute(auth()->user(), PointEventReason::CommentCreated, $comment);

        $this->body = '';
        $this->resetPage(pageName: 'commentsPage');
    }

    public function startReply(int $commentId): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $comment = $this->post->comments()->findOrFail($commentId);

        $this->replyingTo = $comment->id;
        $this->replyBody = '';
        $this->editingId = null;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->replyBody = '';
    }

    public function addReply(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->validateOnly('replyBody', [
            'replyBody' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $parent = $this->post->comments()->findOrFail($this->replyingTo);

        $reply = $this->post->comments()->create([
            'parent_id' => $parent->id,
            'user_id' => auth()->id(),
            'body' => $this->replyBody,
            'status' => 'approved',
        ]);

        // Notify the parent comment author — but not if they're replying to themselves
        if ($parent->user_id !== auth()->id()) {
            $parent->user->notify(new CommentNotification($reply));
        }

        $this->replyingTo = null;
        $this->replyBody = '';
    }

    public function startEdit(int $commentId): void
    {
        $comment = $this->post->comments()->findOrFail($commentId);

        Gate::authorize('update', $comment);

        $this->editingId = $comment->id;
        $this->editBody = $comment->body;
    }

    public function saveEdit(): void
    {
        $comment = $this->post->comments()->findOrFail($this->editingId);

        Gate::authorize('update', $comment);

        $this->validateOnly('editBody', [
            'editBody' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $comment->update(['body' => $this->editBody]);

        $this->editingId = null;
        $this->editBody = '';
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editBody = '';
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->post->comments()->findOrFail($commentId);

        Gate::authorize('delete', $comment);

        $comment->delete();
    }

    public function voteComment(int $commentId): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $comment = $this->post->comments()->with('user')->findOrFail($commentId);

        $existing = Vote::where('user_id', auth()->id())
            ->where('votable_type', Comment::class)
            ->where('votable_id', $comment->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        Vote::create([
            'user_id' => auth()->id(),
            'votable_type' => Comment::class,
            'votable_id' => $comment->id,
            'type' => VoteType::Helpful,
        ]);

        if ($comment->user_id !== auth()->id()) {
            $comment->user->notify(new CommentVoteNotification($comment, auth()->user()));
        }
    }

    public function render(): View
    {
        $comments = $this->post->comments()
            ->with(['user', 'replies.user'])
            ->withCount('votes')
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->latest()
            ->paginate(10, pageName: 'commentsPage');

        // Collect all comment IDs (top-level + replies) to fetch user votes in one query
        $allIds = $comments->getCollection()
            ->flatMap(fn ($comment) => $comment->replies->pluck('id')->prepend($comment->id));

        $userVotedIds = auth()->check()
            ? Vote::where('user_id', auth()->id())
                ->where('votable_type', Comment::class)
                ->whereIn('votable_id', $allIds)
                ->pluck('votable_id')
                ->toArray()
            : [];

        return view('livewire.feed.comment-section', compact('comments', 'userVotedIds'));
    }
}

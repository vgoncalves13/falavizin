<?php

namespace App\Livewire\Feed;

use App\Enums\VoteType;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Vote;
use App\Notifications\CommentNotification;
use App\Notifications\CommentVoteNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class CommentSection extends Component
{
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

        $this->validate();

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

        $this->body = '';
    }

    public function startReply(int $commentId): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->replyingTo = $commentId;
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

        $parent = Comment::findOrFail($this->replyingTo);

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
        $comment = Comment::findOrFail($commentId);

        Gate::authorize('update', $comment);

        $this->editingId = $comment->id;
        $this->editBody = $comment->body;
    }

    public function saveEdit(): void
    {
        $comment = Comment::findOrFail($this->editingId);

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
        $comment = Comment::findOrFail($commentId);

        Gate::authorize('delete', $comment);

        $comment->delete();
    }

    public function voteComment(int $commentId): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $existing = Vote::where('user_id', auth()->id())
            ->where('votable_type', Comment::class)
            ->where('votable_id', $commentId)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        Vote::create([
            'user_id' => auth()->id(),
            'votable_type' => Comment::class,
            'votable_id' => $commentId,
            'type' => VoteType::Helpful,
        ]);

        $comment = Comment::with('post')->findOrFail($commentId);

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
            ->get();

        // Collect all comment IDs (top-level + replies) to fetch user votes in one query
        $allIds = $comments->flatMap(fn ($c) => $c->replies->pluck('id')->prepend($c->id));

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

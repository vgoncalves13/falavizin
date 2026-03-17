<?php

namespace App\Livewire\Feed;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class CommentSection extends Component
{
    public Post $post;

    public string $body = '';

    public ?int $editingId = null;

    public string $editBody = '';

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

        $this->post->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->body,
            'status' => 'approved',
        ]);

        $this->body = '';
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

    public function render(): View
    {
        $comments = $this->post->comments()
            ->with('user')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('livewire.feed.comment-section', compact('comments'));
    }
}

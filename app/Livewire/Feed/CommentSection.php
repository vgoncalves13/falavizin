<?php

namespace App\Livewire\Feed;

use App\Models\Comment;
use App\Models\Post;
use Livewire\Component;

class CommentSection extends Component
{
    public Post $post;

    public string $body = '';

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

    public function deleteComment(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        if (auth()->id() !== $comment->user_id && ! auth()->user()?->is_admin) {
            $this->addError('body', 'Sem permissão para deletar este comentário.');

            return;
        }

        $comment->delete();
    }

    public function render()
    {
        $comments = $this->post->comments()
            ->with('user')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('livewire.feed.comment-section', compact('comments'));
    }
}

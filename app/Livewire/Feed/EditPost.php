<?php

namespace App\Livewire\Feed;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class EditPost extends Component
{
    public Post $post;

    public string $title = '';

    public string $body = '';

    public string $location = '';

    public ?int $categoryId = null;

    public string $eventStartsAt = '';

    public string $eventEndsAt = '';

    public function mount(Post $post): void
    {
        Gate::authorize('update', $post);

        $this->post = $post;
        $this->title = $post->title;
        $this->body = $post->body ?? '';
        $this->location = $post->location ?? '';
        $this->categoryId = $post->category_id;
        $this->eventStartsAt = $post->event_starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->eventEndsAt = $post->event_ends_at?->format('Y-m-d\TH:i') ?? '';
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'eventStartsAt' => ['nullable', 'date'],
            'eventEndsAt' => ['nullable', 'date'],
        ];
    }

    public function save(): void
    {
        Gate::authorize('update', $this->post);

        $this->validate();

        $wasRejected = $this->post->status === PostStatus::Rejected;

        $this->post->update([
            'title' => $this->title,
            'body' => $this->body,
            'category_id' => $this->categoryId,
            'location' => $this->location ?: null,
            'event_starts_at' => $this->eventStartsAt ? Carbon::parse($this->eventStartsAt) : null,
            'event_ends_at' => $this->eventEndsAt ? Carbon::parse($this->eventEndsAt) : null,
        ]);

        if ($wasRejected) {
            $this->post->update([
                'status' => PostStatus::Pending,
                'approved_at' => null,
            ]);

            Cache::forget('admin:moderation_count');

            $message = 'Post atualizado e reenviado para moderação!';
        } else {
            $message = 'Post atualizado com sucesso!';
        }

        $this->redirect(route('neighborhood.feed.show', [
            ...$this->post->neighborhood->routeParameters(),
            'post' => $this->post,
        ]), navigate: false);
        session()->flash('success', $message);
    }

    public function render(): View
    {
        $categories = Category::query()
            ->whereIn('type', ['post', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.feed.edit-post', compact('categories'));
    }
}

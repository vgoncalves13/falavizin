<?php

namespace App\Livewire\Feed;

use App\Actions\CreatePostAction;
use App\Models\Category;
use Livewire\Component;

class CreatePost extends Component
{
    public string $title = '';

    public string $body = '';

    public string $location = '';

    public ?int $categoryId = null;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 5 caracteres.',
            'body.required' => 'O conteúdo é obrigatório.',
            'body.min' => 'O conteúdo deve ter pelo menos 10 caracteres.',
            'categoryId.required' => 'Selecione uma categoria.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $post = (new CreatePostAction)->execute(
            user: auth()->user(),
            data: [
                'title' => $this->title,
                'body' => $this->body,
                'category_id' => $this->categoryId,
                'location' => $this->location ?: null,
            ],
        );

        $this->redirect(route('feed.index'), navigate: false);
        session()->flash('success', 'Post enviado! Aguarda aprovação do admin.');
    }

    public function render()
    {
        $categories = Category::query()
            ->whereIn('type', ['post', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.feed.create-post', compact('categories'));
    }
}

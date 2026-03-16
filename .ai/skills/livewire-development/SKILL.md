# Livewire 4 — Padrões para o Hub do Bairro

Este arquivo sobrescreve a skill built-in do Laravel Boost para Livewire.
Usar **Livewire 4** (não Livewire 3). Atentar para as diferenças de API.

---

## Componentes do Projeto

| Componente | Responsabilidade |
|---|---|
| `Feed\FeedList` | Feed infinito com filtro por categoria |
| `Feed\CreatePost` | Formulário de criação de post |
| `Feed\CommentSection` | Lista + formulário de comentários |
| `Feed\VoteButtons` | Botões de voto útil/não útil |
| `Business\BusinessList` | Lista de negócios com filtro e busca |
| `Business\BusinessForm` | Formulário criar/editar negócio |
| `Business\PromotionForm` | Formulário inline de promoção |
| `Business\ClaimBusiness` | Botão + modal de reivindicação |
| `Home\FeaturedBusinesses` | Grid de negócios em destaque |
| `Home\RecentPromotions` | Promoções ativas na home |

---

## Padrões Obrigatórios

### Propriedades públicas

```php
// ✅ Tipos simples apenas como propriedades públicas
public string $search = '';
public int $perPage = 10;
public bool $showModal = false;

// ❌ NUNCA passar objetos Eloquent como propriedade pública
// public Post $post; // ERRADO — usar ID e buscar no render()
public int $postId = 0; // correto
```

### URL como estado (filtros compartilháveis)

```php
use Livewire\Attributes\Url;

#[Url]
public string $category = '';

#[Url]
public string $search = '';
```

### Lazy loading para listas pesadas

```php
use Livewire\Attributes\Lazy;

#[Lazy]
class FeedList extends Component
{
    // Renderiza placeholder enquanto carrega
}
```

### Paginação com "carregar mais"

```php
use Livewire\WithPagination;

class FeedList extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public function render(): View
    {
        return view('livewire.feed.feed-list', [
            'posts' => Post::approved()
                ->with(['user', 'category'])
                ->latest()
                ->paginate($this->perPage),
        ]);
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
    }
}
```

```blade
{{-- Na view --}}
@if ($posts->hasMorePages())
    <button wire:click="loadMore" wire:loading.attr="disabled">
        <x-heroicon-o-arrow-down class="w-4 h-4 inline" />
        Carregar mais
    </button>
@endif
```

---

## Exemplo Completo: FeedList

```php
<?php

namespace App\Livewire\Feed;

use App\Models\Post;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\View\View;

#[Lazy]
class FeedList extends Component
{
    use WithPagination;

    #[Url]
    public string $category = '';

    #[Url]
    public string $search = '';

    public int $perPage = 10;

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $posts = Post::approved()
            ->with(['user', 'category'])
            ->when($this->category, fn($q) =>
                $q->whereHas('category', fn($q) =>
                    $q->where('slug', $this->category)
                )
            )
            ->when($this->search, fn($q) =>
                $q->where(fn($q) =>
                    $q->where('title', 'like', "%{$this->search}%")
                      ->orWhere('body', 'like', "%{$this->search}%")
                )
            )
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.feed.feed-list', compact('posts'));
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
    }
}
```

---

## Exemplo Completo: VoteButtons

```php
<?php

namespace App\Livewire\Feed;

use App\Actions\VoteOnPostAction;
use App\Enums\VoteType;
use App\Models\Post;
use Livewire\Component;
use Illuminate\View\View;

class VoteButtons extends Component
{
    public int $postId;
    public int $helpfulCount = 0;
    public int $notHelpfulCount = 0;
    public ?string $userVote = null;

    public function mount(Post $post): void
    {
        $this->postId = $post->id;
        $this->helpfulCount = $post->votes()->where('type', VoteType::Helpful)->count();
        $this->notHelpfulCount = $post->votes()->where('type', VoteType::NotHelpful)->count();
        $this->userVote = $post->votes()
            ->where('user_id', auth()->id())
            ->value('type');
    }

    public function vote(string $type): void
    {
        $this->authorize('vote', Post::find($this->postId));

        (new VoteOnPostAction)->execute(
            user: auth()->user(),
            post: Post::find($this->postId),
            type: VoteType::from($type),
        );

        $this->mount(Post::find($this->postId));
    }

    public function render(): View
    {
        return view('livewire.feed.vote-buttons');
    }
}
```

---

## Regras de Autorização em Componentes

```php
// Usar $this->authorize() dentro das actions do componente
public function delete(): void
{
    $post = Post::findOrFail($this->postId);
    $this->authorize('delete', $post);

    $post->delete();
    $this->dispatch('post-deleted');
}
```

---

## Comunicação entre Componentes

```php
// Disparar evento
$this->dispatch('post-created', postId: $post->id);

// Escutar evento
#[On('post-created')]
public function handlePostCreated(int $postId): void
{
    $this->resetPage();
}
```

---

## Feedback Visual com wire:loading

```blade
{{-- Sempre adicionar feedback de loading em ações --}}
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Salvar</span>
    <span wire:loading>Salvando...</span>
</button>

{{-- Skeleton loader para listas com #[Lazy] --}}
<div wire:loading.class.remove="hidden" class="hidden">
    {{-- placeholder skeleton --}}
</div>
```

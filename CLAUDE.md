<laravel-boost-guidelines>
=== .ai/architecture rules ===

# Hub do Bairro — Arquitetura e Convenções Gerais

## Visão Geral do Projeto

**Hub do Bairro** é um portal comunitário hiperlocal (MVP) que combina:

- Feed de acontecimentos do bairro (estilo rede social)
- Classificados de serviços e comércios locais
- Promoções de comerciantes
- Descoberta de serviços por categoria e bairro

**Objetivo do MVP:** lançar rápido, iterar com feedback real. Sem over-engineering.

---

## Stack Obrigatória

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12 |
| Frontend reativo | Livewire 4 |
| JS interações leves | Alpine.js |
| CSS | TailwindCSS 4 |
| Banco de dados | MySQL 8 |
| Autenticação | Laravel Breeze (Blade + Livewire) |
| Storage de imagens | Laravel Storage (disk `public`) |
| Filas | Laravel Queue (driver `database` no MVP) |
| Cache | Laravel Cache (driver `file` no MVP) |

**Não usar no MVP:** Inertia.js, Vue, React, Livewire Volt, Filament.

---

## Convenções de Código

### Geral

- PHP 8.3+. Usar **typed properties**, **enums nativos**, **match expressions**, **named arguments** onde legível.
- Seguir PSR-12.
- Nunca colocar lógica de negócio em controllers. Controllers são finos: recebem request, chamam action/service, retornam resposta.
- Usar **Form Requests** para toda validação de entrada.
- Usar **Laravel Policies** para autorização. Nunca verificar `auth()->id() === $model->user_id` inline em controller.
- Usar **Enums PHP** para campos com valores fixos (status, planos, tipos). Ver `database.md` para regra sobre migrations.

### Nomenclatura

- Models: singular PascalCase (`Post`, `Business`, `Promotion`)
- Migrations: snake_case com prefixo de data gerado pelo artisan
- Livewire components: PascalCase em classe, kebab-case na view (`FeedList` → `feed-list.blade.php`)
- Rotas: kebab-case (`/criar-post`, `/meu-negocio`)
- Blade views: kebab-case em pastas temáticas (`resources/views/feed/create.blade.php`)

### Testes

- Gerar **ao menos um Feature Test** por rota protegida criada.
- Usar `RefreshDatabase` + factories.
- Nomenclatura: `it('pode criar um post no feed')`.

---

## Controllers — Padrão Obrigatório

Controllers são **finos**. Toda lógica vai em Actions:

```php
class PostController extends Controller
{
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = (new CreatePostAction)->execute(
            user: auth()->user(),
            data: $request->validated(),
        );

        return redirect()->route('feed.show', $post)
            ->with('success', 'Post publicado com sucesso!');
    }
}
```

### Actions (app/Actions/)

Criar uma Action class para cada operação de negócio:

- `CreatePostAction`
- `CreateBusinessAction`
- `ClaimBusinessAction`
- `CreatePromotionAction`
- `VoteOnPostAction`
- `ImportBusinessFromGoogleAction`

---

## Rotas

```php
// routes/web.php

// Públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/feed', [PostController::class, 'index'])->name('feed.index');
Route::get('/feed/{post:slug}', [PostController::class, 'show'])->name('feed.show');
Route::get('/servicos', [BusinessController::class, 'index'])->name('businesses.index');
Route::get('/servicos/{business:slug}', [BusinessController::class, 'show'])->name('businesses.show');
Route::get('/categoria/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/promocoes', [PromotionController::class, 'index'])->name('promotions.index');

// Autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/criar-post', [PostController::class, 'create'])->name('feed.create');
    Route::post('/criar-post', [PostController::class, 'store'])->name('feed.store');
    Route::delete('/feed/{post}', [PostController::class, 'destroy'])->name('feed.destroy');

    Route::get('/cadastrar-negocio', [BusinessController::class, 'create'])->name('businesses.create');
    Route::post('/cadastrar-negocio', [BusinessController::class, 'store'])->name('businesses.store');
    Route::get('/meu-negocio/{business}/editar', [BusinessController::class, 'edit'])->name('businesses.edit');
    Route::put('/meu-negocio/{business}', [BusinessController::class, 'update'])->name('businesses.update');

    Route::post('/servicos/{business}/reivindicar', [ClaimBusinessController::class, 'request'])
        ->name('businesses.claim.request');
    Route::get('/reivindicar/{token}', [ClaimBusinessController::class, 'verify'])
        ->name('businesses.claim.verify');

    Route::post('/meu-negocio/{business}/promocoes', [PromotionController::class, 'store'])
        ->name('promotions.store');
    Route::delete('/promocoes/{promotion}', [PromotionController::class, 'destroy'])
        ->name('promotions.destroy');
});

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/moderacao', [ModerationController::class, 'index'])->name('moderation.index');
    Route::post('/moderacao/{type}/{id}/aprovar', [ModerationController::class, 'approve'])->name('moderation.approve');
    Route::post('/moderacao/{type}/{id}/rejeitar', [ModerationController::class, 'reject'])->name('moderation.reject');
});
```

---

## Estrutura de Pastas

```
app/
├── Actions/
│   ├── CreatePostAction.php
│   ├── CreateBusinessAction.php
│   ├── ClaimBusinessAction.php
│   └── ImportBusinessFromGoogleAction.php
├── Console/Commands/
│   └── ImportBusinessesFromGoogle.php
├── Enums/
│   ├── PostStatus.php
│   ├── BusinessPlan.php
│   ├── BusinessStatus.php
│   ├── CategoryType.php
│   └── VoteType.php
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── PostController.php
│   │   ├── BusinessController.php
│   │   ├── CategoryController.php
│   │   ├── PromotionController.php
│   │   ├── ClaimBusinessController.php
│   │   └── Admin/ModerationController.php
│   ├── Middleware/
│   │   └── EnsureUserIsAdmin.php
│   └── Requests/
│       ├── StorePostRequest.php
│       ├── StoreBusinessRequest.php
│       └── StorePromotionRequest.php
├── Livewire/
│   ├── Feed/
│   ├── Business/
│   └── Home/
├── Models/
│   ├── User.php
│   ├── Post.php
│   ├── Comment.php
│   ├── Vote.php
│   ├── Category.php
│   ├── Business.php
│   ├── BusinessPhoto.php
│   └── Promotion.php
└── Policies/
    ├── PostPolicy.php
    └── BusinessPolicy.php

resources/views/
├── layouts/
│   ├── app.blade.php
│   └── admin.blade.php
├── home/index.blade.php
├── feed/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── create.blade.php
├── businesses/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── create.blade.php
├── categories/show.blade.php
├── promotions/index.blade.php
├── admin/moderation/index.blade.php
└── livewire/
    ├── feed/
    ├── business/
    └── home/
```

---

## Páginas — O que cada uma mostra

### Home (`/`)

- Hero com busca por bairro/serviço
- Grid de categorias populares (ícone + nome)
- Negócios em destaque (`plan = featured`)
- Promoções ativas (últimas 4)
- Feed recente (últimos 5 posts aprovados)

### Feed (`/feed`)

- Filtro por categoria (tabs)
- Lista infinita de posts: autor, categoria, data, comentários, votos
- Botão flutuante "Publicar" (autenticados)

### Post individual (`/feed/{slug}`)

- Conteúdo completo, botões de voto, seção de comentários (Livewire)

### Lista de serviços (`/servicos`)

- Barra de busca + filtro por categoria
- Cards de negócios (foto, nome, categoria, bairro, badge "Destaque")
- Negócios `featured` aparecem primeiro

### Perfil do comerciante (`/servicos/{slug}`)

- Foto de capa + galeria, endereço, horário, telefone, WhatsApp
- Promoções ativas do negócio
- Botão "Reivindicar" (se `claimed = false` e usuário autenticado)

### Promoções (`/promocoes`)

- Cards de promoções agrupadas por negócio, badge com validade

---

## Performance — Regras

- Usar `->with([...])` (eager loading) em toda query que renderiza listas. Nunca N+1.
- Usar `Cache::remember()` para queries da home com TTL de 5 minutos.
- Imagens: redimensionar para max 1200px via `Intervention Image` antes de salvar.
- Paginação: preferir `cursorPaginate()` para feeds longos.

---

## Moderação

### Fase 1 — MVP (auto-aprovação)

- Posts, negócios e promoções aprovados automaticamente (`status = 'approved'` como default).
- Usuário autenticado pode reportar conteúdo (campo `reported_at` na entidade).
- Admin vê itens reportados em `/admin/moderacao`.

### Fase 2 — Moderação prévia (pós-MVP)

- Mudar o default de `status` para `'pending'`.
- Admin recebe notificação por email (Laravel `Notification`).

> **MVP usa Fase 1. Estrutura já está pronta para Fase 2 — só mudar o default.**

```php
// app/Http/Middleware/EnsureUserIsAdmin.php
public function handle(Request $request, Closure $next): Response
{
    if (! auth()->user()?->is_admin) {
        abort(403);
    }
    return $next($request);
}
// Registrar como 'admin' no bootstrap/app.php
```

---

## Reivindicação de Negócio

```
1. Comerciante clica em "Reivindicar este negócio"
2. Sistema gera claim_token UUID e salva em businesses.claim_token
3. Email enviado com link: /reivindicar/{token}
4. Comerciante clica no link (deve estar autenticado)
5. Se token válido:
   - businesses.user_id = auth()->id()
   - businesses.claimed = true
   - businesses.claimed_at = now()
   - businesses.claim_token = null  ← invalida o token
6. Comerciante passa a poder editar o perfil
```

```php
// app/Policies/BusinessPolicy.php
public function update(User $user, Business $business): bool
{
    return $user->id === $business->user_id || $user->is_admin;
}
```

---

## Importação via Google Places API

```bash
php artisan businesses:import-google --neighborhood="Copacabana" --type="restaurant"
```

- Chama Google Places Text Search API
- Verifica `google_place_id` para evitar duplicatas
- Cria `Business` com `claimed = false`, `user_id = null`
- Usar Job para processar em fila quando volume for grande

---

## Checklist Antes de Commitar

- [ ] Nenhuma query N+1
- [ ] Todo input passa por Form Request com `authorize()` e `rules()`
- [ ] Toda ação destrutiva tem Policy verificada
- [ ] Imagens salvas via `Storage::disk('public')`
- [ ] Nenhum dado sensível em logs
- [ ] Ao menos 1 teste para cada nova rota protegida

=== .ai/database rules ===

# Hub do Bairro — Banco de Dados

## Regra Crítica: NUNCA usar `$table->enum()` nas migrations

Sempre usar `$table->string()`. O controle de valores é feito via **Enum PHP + cast no Model + validação no Form Request**.

**Motivo:** `enum` no MySQL exige `ALTER TABLE` completo para adicionar valores em produção. `string` + enum PHP é mais flexível e igualmente seguro.

```php
// ❌ NUNCA
$table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');

// ✅ SEMPRE
$table->string('status')->default('approved'); // valores controlados via PostStatus enum PHP
```

---

## Diagrama de Entidades

```
users
  └─< posts (author)
  └─< comments (author)
  └─< votes (voter)
  └─< businesses (owner — nullable se não reivindicado)

posts
  └─< comments
  └─< votes
  └─ belongs_to category

businesses
  └─< promotions
  └─< business_photos
  └─ belongs_to category
  └─ belongs_to user (nullable — owner)

categories (compartilhada entre posts e businesses via type)

promotions
  └─ belongs_to business
```

---

## Migrations — Campos Principais

### `users`

```php
$table->id();
$table->string('name');
$table->string('email')->unique();
$table->string('password');
$table->string('phone')->nullable();
$table->string('neighborhood')->nullable();
$table->boolean('is_admin')->default(false);
$table->timestamps();
```

### `categories`

```php
$table->id();
$table->string('name');
$table->string('slug')->unique();
$table->string('icon')->nullable();      // nome do heroicon sem prefixo: 'bolt', 'home', etc.
$table->string('type')->default('both'); // 'post' | 'business' | 'both' — via CategoryType enum
$table->integer('sort_order')->default(0);
$table->timestamps();
```

### `posts`

```php
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->foreignId('category_id')->constrained();
$table->string('title');
$table->text('body');
$table->string('location')->nullable();
$table->string('status')->default('approved'); // 'pending' | 'approved' | 'rejected' — via PostStatus enum
$table->timestamp('approved_at')->nullable();
$table->timestamps();
$table->softDeletes();

$table->index(['status', 'created_at']);
$table->index('category_id');
```

### `comments`

```php
$table->id();
$table->foreignId('post_id')->constrained()->cascadeOnDelete();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->text('body');
$table->string('status')->default('approved'); // 'approved' | 'rejected' — via enum PHP
$table->timestamps();
$table->softDeletes();
```

### `votes`

```php
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->morphs('votable');
$table->string('type'); // 'helpful' | 'not_helpful' — via VoteType enum
$table->timestamps();

$table->unique(['user_id', 'votable_type', 'votable_id']);
```

### `businesses`

```php
$table->id();
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('category_id')->constrained();
$table->string('name');
$table->string('slug')->unique();
$table->text('description')->nullable();
$table->string('phone')->nullable();
$table->string('whatsapp')->nullable();
$table->string('address')->nullable();
$table->string('neighborhood');
$table->string('city')->default('');
$table->json('opening_hours')->nullable(); // {"seg":"08:00-18:00","ter":"08:00-18:00"}
$table->string('website')->nullable();
$table->decimal('lat', 10, 8)->nullable();
$table->decimal('lng', 11, 8)->nullable();
$table->string('google_place_id')->nullable()->unique();
$table->string('plan')->default('free');       // 'free' | 'featured' — via BusinessPlan enum
$table->string('status')->default('approved'); // 'pending' | 'approved' | 'rejected' — via BusinessStatus enum
$table->boolean('claimed')->default(false);
$table->string('claim_token')->nullable();
$table->timestamp('claimed_at')->nullable();
$table->timestamps();
$table->softDeletes();

$table->index(['neighborhood', 'status']);
$table->index(['plan', 'status']);
$table->index('category_id');
```

### `business_photos`

```php
$table->id();
$table->foreignId('business_id')->constrained()->cascadeOnDelete();
$table->string('path');
$table->boolean('is_cover')->default(false);
$table->integer('sort_order')->default(0);
$table->timestamps();
```

### `promotions`

```php
$table->id();
$table->foreignId('business_id')->constrained()->cascadeOnDelete();
$table->string('title');
$table->text('description')->nullable();
$table->date('starts_at')->nullable();
$table->date('ends_at')->nullable();
$table->boolean('is_active')->default(true);
$table->string('status')->default('approved'); // 'pending' | 'approved' | 'rejected' — via enum PHP
$table->timestamps();
$table->softDeletes();

$table->index(['is_active', 'ends_at', 'status']);
```

---

## Enums PHP (app/Enums/)

```php
// PostStatus.php
enum PostStatus: string {
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

// BusinessPlan.php
enum BusinessPlan: string {
    case Free     = 'free';
    case Featured = 'featured';
}

// BusinessStatus.php
enum BusinessStatus: string {
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

// CategoryType.php
enum CategoryType: string {
    case Post     = 'post';
    case Business = 'business';
    case Both     = 'both';
}

// VoteType.php
enum VoteType: string {
    case Helpful    = 'helpful';
    case NotHelpful = 'not_helpful';
}
```

---

## Casts nos Models — Obrigatório

```php
// Post
protected $casts = [
    'status' => PostStatus::class,
];

// Business
protected $casts = [
    'plan'          => BusinessPlan::class,
    'status'        => BusinessStatus::class,
    'opening_hours' => 'array',
];

// Vote
protected $casts = [
    'type' => VoteType::class,
];

// Category
protected $casts = [
    'type' => CategoryType::class,
];
```

---

## Validação nos Form Requests — Obrigatório

```php
use Illuminate\Validation\Rules\Enum;

// StorePostRequest
'status' => ['sometimes', new Enum(PostStatus::class)],

// StoreBusinessRequest
'plan' => ['sometimes', new Enum(BusinessPlan::class)],
```

---

## Models — Relacionamentos e Scopes

```php
// User
public function posts(): HasMany { return $this->hasMany(Post::class); }
public function businesses(): HasMany { return $this->hasMany(Business::class); }
public function comments(): HasMany { return $this->hasMany(Comment::class); }

// Post
public function user(): BelongsTo { return $this->belongsTo(User::class); }
public function category(): BelongsTo { return $this->belongsTo(Category::class); }
public function comments(): HasMany { return $this->hasMany(Comment::class); }
public function votes(): MorphMany { return $this->morphMany(Vote::class, 'votable'); }

public function scopeApproved(Builder $q): Builder {
    return $q->where('status', PostStatus::Approved);
}

// Business
public function user(): BelongsTo { return $this->belongsTo(User::class); }
public function category(): BelongsTo { return $this->belongsTo(Category::class); }
public function photos(): HasMany { return $this->hasMany(BusinessPhoto::class); }
public function promotions(): HasMany { return $this->hasMany(Promotion::class); }
public function coverPhoto(): HasOne {
    return $this->hasOne(BusinessPhoto::class)->where('is_cover', true);
}

public function scopeFeatured(Builder $q): Builder {
    return $q->where('plan', BusinessPlan::Featured)->where('status', BusinessStatus::Approved);
}
public function scopeInNeighborhood(Builder $q, string $neighborhood): Builder {
    return $q->where('neighborhood', 'like', "%{$neighborhood}%");
}

// Promotion
public function business(): BelongsTo { return $this->belongsTo(Business::class); }

public function scopeActive(Builder $q): Builder {
    return $q->where('is_active', true)
             ->where('status', 'approved')
             ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
}
```

=== .ai/frontend rules ===

# Hub do Bairro — Frontend, Ícones e Design

## Ícones — Heroicons (Obrigatório)

**Biblioteca:** `blade-ui-kit/blade-heroicons`

```bash
composer require blade-ui-kit/blade-heroicons
```

### Uso nas views Blade

```blade
{{-- Outline (padrão para UI geral) --}}
<x-heroicon-o-home class="w-5 h-5" />
<x-heroicon-o-chat-bubble-left class="w-5 h-5" />
<x-heroicon-o-hand-thumb-up class="w-5 h-5" />
<x-heroicon-o-map-pin class="w-5 h-5" />
<x-heroicon-o-phone class="w-5 h-5" />
<x-heroicon-o-megaphone class="w-5 h-5" />
<x-heroicon-o-star class="w-5 h-5" />

{{-- Solid (estados ativos, badges, destaques) --}}
<x-heroicon-s-star class="w-5 h-5 text-amber-500" />
<x-heroicon-s-bolt class="w-4 h-4 text-amber-500" />

{{-- Ícone dinâmico (para categorias vindas do banco) --}}
<x-dynamic-component
    :component="'heroicon-o-' . $category->icon"
    class="w-6 h-6"
/>
```

### Regras absolutas

- **NUNCA** usar emoji como ícone na interface (`🏠`, `⚡`, etc.)
- **NUNCA** usar caracteres unicode como substituto de ícone
- **NUNCA** usar Font Awesome, Material Icons ou qualquer outra biblioteca — apenas Heroicons
- O campo `icon` na tabela `categories` armazena o **nome sem prefixo**: `'bolt'`, `'home'`, `'shopping-cart'`

### Mapeamento categorias → heroicon

```
aviso           → megaphone
problema        → exclamation-triangle
evento          → calendar-days
achado-perdido  → magnifying-glass
alimentacao     → cake
mercado         → shopping-cart
saude           → heart
pet             → face-smile
eletrica        → bolt
encanamento     → wrench
pintura         → paint-brush
internet        → wifi
educacao        → academic-cap
beleza          → sparkles
```

---

## Design — Direção Estética do Projeto

### Identidade visual

O Hub do Bairro deve parecer um **quadro de avisos digital vivo** — acolhedor, local e humano. Não um SaaS corporativo. Não um app branco com botão azul.

**Tom:** comunitário, próximo, confiável. Como se o bairro tivesse sua própria voz.

### Tipografia

- **Display/Títulos:** `Plus Jakarta Sans` ou `Sora` (Google Fonts)
- **Corpo:** `DM Sans` ou `Nunito`
- **NUNCA usar:** Inter, Roboto, Arial, system-ui como fonte principal

```html
<!-- No layout principal -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
```

```css
/* resources/css/app.css */
:root {
    --font-display: 'Plus Jakarta Sans', sans-serif;
    --font-body: 'DM Sans', sans-serif;

    --color-primary: #d97706;      /* amber-600 — cor dominante */
    --color-primary-dark: #b45309; /* amber-700 */
    --color-accent: #f59e0b;       /* amber-400 */
    --color-surface: #fafaf9;      /* stone-50 — fundo off-white, não branco puro */
    --color-border: #e7e5e4;       /* stone-200 */
    --color-text: #1c1917;         /* stone-900 */
    --color-muted: #78716c;        /* stone-500 */
}
```

### Paleta de cores

| Papel | Cor | Tailwind |
|---|---|---|
| Primária (CTA, links, badges) | Âmbar/laranja | `amber-600` |
| Fundo das páginas | Off-white quente | `stone-50` |
| Cards e superfícies | Branco | `white` |
| Bordas | Cinza quente claro | `stone-200` |
| Texto principal | Quase preto | `stone-900` |
| Texto secundário | Cinza médio | `stone-500` |
| Destaque/featured | Âmbar claro | `amber-50` com borda `amber-300` |

### Componentes Blade reutilizáveis obrigatórios

Criar componentes Blade para todos os elementos repetidos — nunca duplicar HTML:

```
resources/views/components/
├── post-card.blade.php          <!-- card do feed -->
├── business-card.blade.php      <!-- card de negócio na lista -->
├── category-badge.blade.php     <!-- badge colorido de categoria -->
├── promotion-card.blade.php     <!-- card de promoção -->
├── avatar.blade.php             <!-- avatar de usuário -->
├── section-title.blade.php      <!-- título de seção padronizado -->
└── whatsapp-button.blade.php    <!-- botão verde do WhatsApp -->
```

### Micro-interações com Alpine.js + Tailwind

```blade
{{-- Hover em cards --}}
<div class="... transition-shadow duration-200 hover:shadow-md">

{{-- Botão com feedback --}}
<button
    x-data="{ loading: false }"
    @click="loading = true"
    :class="loading ? 'opacity-75 cursor-not-allowed' : ''"
    class="... transition-all duration-150"
>
    <span x-show="!loading">Publicar</span>
    <span x-show="loading">Publicando...</span>
</button>

{{-- Toggle de filtro ativo --}}
<button
    :class="active ? 'bg-amber-600 text-white' : 'bg-white text-stone-700 hover:bg-stone-100'"
    class="... transition-colors duration-150"
>
```

### Layout e hierarquia visual

- Negócios **featured** devem ser visivelmente diferentes dos free — usar borda âmbar, badge "Destaque", leve sombra
- Cards do feed: foto/avatar do autor à esquerda, conteúdo à direita, categoria como badge colorido
- Hero da home: fundo com textura sutil (padrão geométrico leve ou gradiente warm), título grande e buscador central
- Grid de categorias: ícone grande + nome, hover com cor de fundo da categoria

### O que NÃO fazer no frontend

- Não usar `card com shadow-sm e rounded-lg` genérico sem personalidade
- Não usar gradiente roxo/azul em nenhum elemento
- Não usar botões com `bg-blue-600` — a cor primária é âmbar
- Não centralizar tudo — usar assimetria e hierarquia nos layouts
- Não usar placeholder "Lorem ipsum" em nenhum componente gerado

=== .ai/project-data rules ===

# Hub do Bairro — Dados do Projeto

## Seed de Categorias (obrigatório no DatabaseSeeder)

O campo `icon` armazena o nome do heroicon sem prefixo.
Renderizar com: `<x-dynamic-component :component="'heroicon-o-' . $category->icon" />`

```php
$categories = [
    // Para posts do feed
    ['name' => 'Aviso',            'slug' => 'aviso',          'icon' => 'megaphone',            'type' => 'post',     'sort_order' => 1],
    ['name' => 'Problema',         'slug' => 'problema',       'icon' => 'exclamation-triangle',  'type' => 'post',     'sort_order' => 2],
    ['name' => 'Evento',           'slug' => 'evento',         'icon' => 'calendar-days',         'type' => 'post',     'sort_order' => 3],
    ['name' => 'Achado e Perdido', 'slug' => 'achado-perdido', 'icon' => 'magnifying-glass',      'type' => 'post',     'sort_order' => 4],

    // Para negócios e ambos
    ['name' => 'Alimentação',      'slug' => 'alimentacao',    'icon' => 'cake',                  'type' => 'both',     'sort_order' => 5],
    ['name' => 'Mercado',          'slug' => 'mercado',        'icon' => 'shopping-cart',         'type' => 'business', 'sort_order' => 6],
    ['name' => 'Saúde',            'slug' => 'saude',          'icon' => 'heart',                 'type' => 'business', 'sort_order' => 7],
    ['name' => 'Pet',              'slug' => 'pet',            'icon' => 'face-smile',            'type' => 'business', 'sort_order' => 8],
    ['name' => 'Elétrica',         'slug' => 'eletrica',       'icon' => 'bolt',                  'type' => 'business', 'sort_order' => 9],
    ['name' => 'Encanamento',      'slug' => 'encanamento',    'icon' => 'wrench',                'type' => 'business', 'sort_order' => 10],
    ['name' => 'Pintura',          'slug' => 'pintura',        'icon' => 'paint-brush',           'type' => 'business', 'sort_order' => 11],
    ['name' => 'Internet',         'slug' => 'internet',       'icon' => 'wifi',                  'type' => 'business', 'sort_order' => 12],
    ['name' => 'Educação',         'slug' => 'educacao',       'icon' => 'academic-cap',          'type' => 'business', 'sort_order' => 13],
    ['name' => 'Beleza',           'slug' => 'beleza',         'icon' => 'sparkles',              'type' => 'business', 'sort_order' => 14],
];

foreach ($categories as $category) {
    Category::firstOrCreate(['slug' => $category['slug']], $category);
}
```

---

## Variáveis de Ambiente

```env
APP_NAME="Hub do Bairro"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_DATABASE=hub_do_bairro

QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file

MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="noreply@hudobairro.com.br"
MAIL_FROM_NAME="Hub do Bairro"

GOOGLE_PLACES_API_KEY=

# Opcional no MVP — necessário apenas para o command de importação

FILESYSTEM_DISK=public
```

---

## Roadmap de Desenvolvimento — 4 Semanas

### Semana 1 — Base e Autenticação

**Meta: projeto rodando com auth e estrutura completa de banco**

- [ ] `laravel new hub-do-bairro` com Breeze (Blade + Livewire)
- [ ] Instalar: Livewire 4, Alpine.js, TailwindCSS 4
- [ ] Instalar: `blade-ui-kit/blade-heroicons`
- [ ] Instalar: `composer require laravel/boost --dev` + `php artisan boost:install`
- [ ] Criar todas as migrations (conforme `database.md`)
- [ ] Criar todos os Models com relacionamentos, scopes e casts
- [ ] Criar todos os Enums em `app/Enums/`
- [ ] Seed de categorias
- [ ] Definir paleta e fontes no `app.css` (conforme `frontend.md`)
- [ ] Layout principal (`layouts/app.blade.php`) com nav responsiva usando Heroicons
- [ ] Páginas estáticas: Home (esqueleto), Feed (esqueleto)

**Critério de done:** `php artisan migrate:fresh --seed` sem erros. Home abre no browser com nav funcional.

---

### Semana 2 — Feed do Bairro

**Meta: moradores publicando e comentando**

- [ ] Componente `Feed\FeedList` com paginação e filtro por categoria
- [ ] Componente `Feed\CreatePost` com validação
- [ ] Componente `Feed\CommentSection`
- [ ] Componente `Feed\VoteButtons`
- [ ] Página de post individual (`/feed/{slug}`)
- [ ] Policy `PostPolicy`
- [ ] Componente Blade `<x-post-card>`
- [ ] Componente Blade `<x-category-badge>`
- [ ] Factories + Feature Tests para Post e Comment

**Critério de done:** usuário cria post, outro usuário comenta e vota. Admin deleta post problemático.

---

### Semana 3 — Classificados e Perfis de Negócio

**Meta: comerciantes com perfil público**

- [ ] Componente `Business\BusinessForm` (criar negócio)
- [ ] Componente `Business\BusinessList` com filtro e busca
- [ ] Página de perfil do comerciante (`/servicos/{slug}`)
- [ ] Upload de fotos do negócio (storage public + Intervention Image)
- [ ] Componente `Business\ClaimBusiness` + fluxo de reivindicação por email
- [ ] Componente `Business\PromotionForm`
- [ ] Página de promoções (`/promocoes`)
- [ ] Policy `BusinessPolicy`
- [ ] Componente Blade `<x-business-card>`
- [ ] Componente Blade `<x-whatsapp-button>`

**Critério de done:** negócio cadastrado aparece na lista. Comerciante reivindica via email e passa a editar o perfil.

---

### Semana 4 — Home, Destaque e Lançamento

**Meta: pronto para colocar no ar**

- [ ] Home completa: grid de categorias, negócios em destaque, promoções, feed recente
- [ ] Flag `plan = featured` funcional (admin seta via Tinker)
- [ ] Painel de moderação admin (`/admin/moderacao`)
- [ ] Command `businesses:import-google` (ao menos stub funcional)
- [ ] `Cache::remember()` na home (TTL 5 min)
- [ ] SEO básico: meta tags dinâmicas (`<title>`, `og:title`, `og:description`)
- [ ] Testes de fumaça nas rotas principais
- [ ] Deploy em VPS com MySQL + fila `database`

**Critério de done:** URL pública funcionando com usuário real criando post e negócio aparecendo na home.

---

## O que NÃO Implementar no MVP

> Se o Claude Code sugerir qualquer item abaixo, recusar e anotar para roadmap futuro.

- Pagamentos online (Stripe, Pagar.me, Mercado Pago)
- Chat interno entre usuários
- Sistema de reputação / badges / pontuação
- Notificações push (FCM, Pusher, Reverb)
- App mobile (React Native, Flutter)
- Multi-tenant / múltiplos bairros — focar em 1 bairro primeiro
- Elasticsearch ou Meilisearch — usar MySQL `LIKE` por ora
- Laravel Horizon ou Telescope — adicionar após lançar
- WebSockets / broadcasting em tempo real
- Painel admin complexo (Filament, Nova) — admin via rotas simples no MVP

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5.3
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `vendor/bin/sail artisan route:list`, `vendor/bin/sail artisan tinker --execute "..."`).
- Use `vendor/bin/sail artisan list` to discover available commands and `vendor/bin/sail artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `vendor/bin/sail artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `vendor/bin/sail artisan config:show [key]`.
- To inspect routes, run `vendor/bin/sail artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `vendor/bin/sail artisan list` and check their parameters with `vendor/bin/sail artisan [command] --help`.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `vendor/bin/sail artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `vendor/bin/sail artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `vendor/bin/sail artisan test --compact`.
- To run all tests in a file: `vendor/bin/sail artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `vendor/bin/sail artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

</laravel-boost-guidelines>

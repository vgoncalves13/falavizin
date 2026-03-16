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

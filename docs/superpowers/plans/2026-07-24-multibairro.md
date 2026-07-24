# FalaVizin Multibairro Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Adicionar bairros canônicos ao FalaVizin, isolar todo conteúdo local pela URL e permitir seleção, cadastro e administração de bairros sem misturar comunidades.

**Architecture:** `Neighborhood` será a entidade canônica. Usuários terão um bairro principal, posts terão bairro próprio e negócios terão um bairro principal de publicação. Rotas locais usarão o prefixo `/{state}/{city}/{neighborhood}`, consultas receberão o bairro explicitamente e caches serão segmentados por ID. A primeira release usa foreign keys nullable e escrita dupla; a migration de contrato só será criada após validação em produção.

**Tech Stack:** Laravel 12, Livewire 4, PHP 8.5, MySQL 8, Blade, Alpine.js, Tailwind CSS 4, PHPUnit 11, Laravel Sail.

---

## Limite desta execução

Este plano entrega as cinco etapas funcionais da especificação e a release de
expansão. Ele não cria a migration de contrato que remove
`users.neighborhood` e `businesses.neighborhood`. Depois do deploy, execute:

```bash
rtk docker compose --env-file .env -f compose.production.yaml exec -u www-data web \
    php artisan neighborhoods:audit
```

O comando deve retornar zero posts e negócios sem `neighborhood_id`. Somente
depois de ao menos um deploy estável deve ser criado o plano de contrato.

## Estrutura de arquivos

### Criações

- `app/Models/Neighborhood.php`: entidade, URL e escopos.
- `database/factories/NeighborhoodFactory.php`: dados de teste.
- `database/migrations/2026_07_24_120000_create_neighborhoods_table.php`: tabela e bairro piloto.
- `database/migrations/2026_07_24_120100_add_neighborhood_id_to_local_entities.php`: foreign keys nullable, índices e backfill.
- `app/Http/Middleware/ResolveNeighborhood.php`: compartilha e memoriza o bairro da rota.
- `app/Http/Middleware/EnsureNeighborhoodIsActive.php`: bloqueia listagens e gravações em bairro inativo.
- `app/Http/Middleware/EnsurePrimaryNeighborhood.php`: envia usuários sem bairro válido ao onboarding.
- `app/Http/Controllers/LegacyNeighborhoodRedirectController.php`: redirects das URLs antigas.
- `app/Http/Controllers/NeighborhoodSelectionController.php`: seleção do bairro principal.
- `app/Http/Requests/Auth/RegisterUserRequest.php`: valida cadastro com bairro.
- `app/Http/Requests/UpdatePrimaryNeighborhoodRequest.php`: valida alteração do bairro principal.
- `app/Actions/UpdatePrimaryNeighborhoodAction.php`: altera o bairro principal.
- `app/Actions/SaveNeighborhoodAction.php`: cria e atualiza bairro.
- `app/Actions/SetNeighborhoodStatusAction.php`: ativa ou desativa com lock.
- `app/Services/NeighborhoodCache.php`: chaves e invalidação de caches locais.
- `app/Observers/{Post,Business,Promotion,Category,User,Neighborhood}Observer.php`: delegam invalidação.
- `app/Livewire/Admin/NeighborhoodManager.php`: administração simples.
- `resources/views/livewire/admin/neighborhood-manager.blade.php`: formulário e listagem.
- `resources/views/neighborhoods/index.blade.php`: diretório público e onboarding.
- `resources/views/components/neighborhood-switcher.blade.php`: seletor desktop/mobile.
- `resources/views/components/inactive-neighborhood-banner.blade.php`: aviso histórico.
- `app/Console/Commands/AuditNeighborhoodAssignments.php`: checkpoint da release.
- testes de migration, rotas, isolamento, cache, autenticação, administração e bairros inativos.

### Remoções

- `app/Livewire/Admin/AppSettings.php`.
- `resources/views/livewire/admin/app-settings.blade.php`.

### Modificações principais

- models, factories, seed, Actions, Policies e componentes Livewire de posts e negócios;
- controllers de home, feed, busca, categorias, serviços, promoções, Pulso, perfil, autenticação e sitemap;
- `routes/web.php`, `routes/auth.php`, `bootstrap/app.php` e `app/Providers/AppServiceProvider.php`;
- navbar, home, feed, serviços, eventos, busca, Pulso, perfil e componentes de card;
- notificações que geram URLs de posts ou negócios.

---

## Entrega 1 — Fundação de dados

### Task 1: Criar o schema de bairros e testar o upgrade

**Files:**
- Create: `database/migrations/2026_07_24_120000_create_neighborhoods_table.php`
- Create: `database/migrations/2026_07_24_120100_add_neighborhood_id_to_local_entities.php`
- Create: `tests/Feature/NeighborhoodMigrationTest.php`

- [ ] **Step 1: Escrever o teste de upgrade que parte do schema anterior**

Use `DatabaseMigrations`, derrube apenas as duas migrations novas, insira dados
legados com Query Builder, execute `up()` e confira o backfill:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NeighborhoodMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_expansion_migration_backfills_all_local_entities(): void
    {
        $relations = require database_path('migrations/2026_07_24_120100_add_neighborhood_id_to_local_entities.php');
        $neighborhoods = require database_path('migrations/2026_07_24_120000_create_neighborhoods_table.php');

        $relations->down();
        $neighborhoods->down();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Morador legado',
            'email' => 'legado@example.com',
            'password' => bcrypt('password'),
            'neighborhood' => 'Engenho da Rainha',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Aviso',
            'slug' => 'aviso-legado',
            'type' => 'post',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('posts')->insert([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => 'Post legado',
            'slug' => 'post-legado',
            'body' => 'Conteúdo legado para o teste.',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('businesses')->insert([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'name' => 'Negócio legado',
            'slug' => 'negocio-legado',
            'neighborhood' => 'Engenho da Rainha',
            'city' => 'Rio de Janeiro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $neighborhoods->up();
        $relations->up();

        $pilotId = DB::table('neighborhoods')
            ->where('slug', 'engenho-da-rainha')
            ->value('id');

        $this->assertNotNull($pilotId);
        $this->assertSame(0, DB::table('users')->whereNull('neighborhood_id')->count());
        $this->assertSame(0, DB::table('posts')->whereNull('neighborhood_id')->count());
        $this->assertSame(0, DB::table('businesses')->whereNull('neighborhood_id')->count());
    }
}
```

- [ ] **Step 2: Executar o teste e confirmar a falha inicial**

Run:

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodMigrationTest.php
```

Expected: FAIL porque os arquivos de migration ainda não existem.

- [ ] **Step 3: Criar a migration determinística de `neighborhoods`**

Use `string`, nunca `$table->enum()`:

```php
Schema::create('neighborhoods', function (Blueprint $table): void {
    $table->id();
    $table->string('name');
    $table->string('slug');
    $table->string('city');
    $table->string('city_slug');
    $table->string('state_code', 2);
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['state_code', 'city_slug', 'slug']);
    $table->index(['is_active', 'sort_order']);
});

$latitude = DB::table('settings')->where('key', 'neighborhood_lat')->value('value');
$longitude = DB::table('settings')->where('key', 'neighborhood_lng')->value('value');

DB::table('neighborhoods')->insertOrIgnore([
    'name' => 'Engenho da Rainha',
    'slug' => 'engenho-da-rainha',
    'city' => 'Rio de Janeiro',
    'city_slug' => 'rio-de-janeiro',
    'state_code' => 'RJ',
    'latitude' => is_numeric($latitude) ? $latitude : null,
    'longitude' => is_numeric($longitude) ? $longitude : null,
    'is_active' => true,
    'sort_order' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);
```

O `down()` usa apenas `Schema::dropIfExists('neighborhoods')`.

- [ ] **Step 4: Criar a migration nullable, índices e backfill**

```php
Schema::table('users', function (Blueprint $table): void {
    $table->foreignId('neighborhood_id')
        ->nullable()
        ->after('neighborhood')
        ->constrained()
        ->restrictOnDelete();
});

Schema::table('posts', function (Blueprint $table): void {
    $table->foreignId('neighborhood_id')
        ->nullable()
        ->after('user_id')
        ->constrained()
        ->restrictOnDelete();
    $table->index(
        ['neighborhood_id', 'status', 'created_at'],
        'posts_neighborhood_status_created_index',
    );
    $table->index(
        ['neighborhood_id', 'status', 'event_starts_at'],
        'posts_neighborhood_status_event_index',
    );
});

Schema::table('businesses', function (Blueprint $table): void {
    $table->foreignId('neighborhood_id')
        ->nullable()
        ->after('user_id')
        ->constrained()
        ->restrictOnDelete();
    $table->index(
        ['neighborhood_id', 'status', 'category_id'],
        'businesses_neighborhood_status_category_index',
    );
});

$pilotId = DB::table('neighborhoods')
    ->where('state_code', 'RJ')
    ->where('city_slug', 'rio-de-janeiro')
    ->where('slug', 'engenho-da-rainha')
    ->value('id');

throw_unless($pilotId, RuntimeException::class, 'Pilot neighborhood was not created.');

foreach (['users', 'posts', 'businesses'] as $table) {
    DB::table($table)->whereNull('neighborhood_id')->update(['neighborhood_id' => $pilotId]);
}
```

O `down()` remove primeiro índices e foreign keys, depois as três colunas.

- [ ] **Step 5: Executar o teste de migration**

Run:

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodMigrationTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add database/migrations tests/Feature/NeighborhoodMigrationTest.php
rtk git commit -m "feat: add canonical neighborhoods schema"
```

### Task 2: Adicionar model, relações, factories e URLs canônicas

**Files:**
- Create: `app/Models/Neighborhood.php`
- Create: `database/factories/NeighborhoodFactory.php`
- Create: `tests/Feature/NeighborhoodModelTest.php`
- Modify: `app/Models/User.php`
- Modify: `app/Models/Post.php`
- Modify: `app/Models/Business.php`
- Modify: `database/factories/UserFactory.php`
- Modify: `database/factories/PostFactory.php`
- Modify: `database/factories/BusinessFactory.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Escrever testes de escopo, URL e regra de interação**

```php
public function test_neighborhood_exposes_normalized_route_parameters(): void
{
    $neighborhood = Neighborhood::factory()->create([
        'state_code' => 'RJ',
        'city_slug' => 'rio-de-janeiro',
        'slug' => 'engenho-da-rainha',
    ]);

    $this->assertSame([
        'state' => 'rj',
        'city' => 'rio-de-janeiro',
        'neighborhood' => $neighborhood,
    ], $neighborhood->routeParameters());
}

public function test_inactive_neighborhood_rejects_community_interactions(): void
{
    $neighborhood = Neighborhood::factory()->inactive()->create();

    $this->assertFalse(Post::factory()->for($neighborhood)->create()->acceptsCommunityInteractions());
    $this->assertFalse(
        Business::factory()->create(['neighborhood_id' => $neighborhood->id])
            ->acceptsCommunityInteractions(),
    );
}
```

- [ ] **Step 2: Executar o teste e confirmar a falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodModelTest.php
```

Expected: FAIL com `Class "App\Models\Neighborhood" not found`.

- [ ] **Step 3: Criar `Neighborhood`**

```php
class Neighborhood extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'city', 'city_slug', 'state_code',
        'latitude', 'longitude', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function routeParameters(): array
    {
        return [
            'state' => strtolower($this->state_code),
            'city' => $this->city_slug,
            'neighborhood' => $this,
        ];
    }
}
```

- [ ] **Step 4: Adicionar relações e helpers aos models existentes**

Use nomes que não colidam com os campos legados:

```php
// User
public function primaryNeighborhood(): BelongsTo
{
    return $this->belongsTo(Neighborhood::class, 'neighborhood_id');
}

// Post
public function neighborhood(): BelongsTo
{
    return $this->belongsTo(Neighborhood::class);
}

public function scopeForNeighborhood(Builder $query, Neighborhood|int $neighborhood): Builder
{
    return $query->where('neighborhood_id', $neighborhood instanceof Neighborhood
        ? $neighborhood->getKey()
        : $neighborhood);
}

public function acceptsCommunityInteractions(): bool
{
    return (bool) $this->neighborhood?->is_active;
}

public function canonicalUrl(bool $absolute = true): string
{
    return route('neighborhood.feed.show', [
        ...$this->neighborhood->routeParameters(),
        'post' => $this,
    ], $absolute);
}

// Business
public function localNeighborhood(): BelongsTo
{
    return $this->belongsTo(Neighborhood::class, 'neighborhood_id');
}

public function scopeForNeighborhood(Builder $query, Neighborhood|int $neighborhood): Builder
{
    return $query->where('neighborhood_id', $neighborhood instanceof Neighborhood
        ? $neighborhood->getKey()
        : $neighborhood);
}

public function acceptsCommunityInteractions(): bool
{
    return (bool) $this->localNeighborhood?->is_active;
}

public function canonicalUrl(bool $absolute = true): string
{
    return route('neighborhood.businesses.show', [
        ...$this->localNeighborhood->routeParameters(),
        'business' => $this,
    ], $absolute);
}
```

Inclua `neighborhood_id` nos `$fillable`.

- [ ] **Step 5: Atualizar factories e seeder**

Factory:

```php
// NeighborhoodFactory
return [
    'name' => fake()->citySuffix(),
    'slug' => fake()->unique()->slug(),
    'city' => 'Rio de Janeiro',
    'city_slug' => 'rio-de-janeiro',
    'state_code' => 'RJ',
    'is_active' => true,
    'sort_order' => fake()->unique()->numberBetween(1, 10_000),
];

public function inactive(): static
{
    return $this->state(['is_active' => false]);
}
```

Adicione `neighborhood_id => Neighborhood::factory()` às factories de User,
Post e Business. Durante a expansão, mantenha `neighborhood => 'Engenho da
Rainha'` nas factories de User e Business.

No `DatabaseSeeder`, use `Neighborhood::firstOrCreate()` para o piloto, associe
seu ID a todos os usuários, posts e negócios demonstrativos e troque a constante
de bairro para `Engenho da Rainha`.

- [ ] **Step 6: Executar testes focados**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodModelTest.php \
    tests/Feature/DatabaseSeederTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Models database/factories database/seeders tests/Feature
rtk git commit -m "feat: model neighborhood ownership"
```

---

## Entrega 2 — Contexto e rotas

### Task 3: Resolver o bairro da URL e proteger rotas locais

**Files:**
- Create: `app/Http/Middleware/ResolveNeighborhood.php`
- Create: `app/Http/Middleware/EnsureNeighborhoodIsActive.php`
- Create: `app/Http/Controllers/LegacyNeighborhoodRedirectController.php`
- Create: `tests/Feature/NeighborhoodRoutingTest.php`
- Modify: `bootstrap/app.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app/Http/Controllers/HomeController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Escrever testes de rota composta, escopo e legado**

```php
public function test_local_route_resolves_the_full_neighborhood_path(): void
{
    $neighborhood = Neighborhood::factory()->create();

    $this->get(route('neighborhood.home', $neighborhood->routeParameters()))
        ->assertOk()
        ->assertViewHas('neighborhood', $neighborhood);
}

public function test_post_under_the_wrong_neighborhood_returns_not_found(): void
{
    $correct = Neighborhood::factory()->create();
    $wrong = Neighborhood::factory()->create();
    $post = Post::factory()->for($correct)->create();

    $this->get(route('neighborhood.feed.show', [
        ...$wrong->routeParameters(),
        'post' => $post,
    ]))->assertNotFound();
}

public function test_legacy_post_url_redirects_permanently_to_canonical_url(): void
{
    $post = Post::factory()->create();

    $this->get(route('feed.show', $post))
        ->assertRedirect($post->canonicalUrl())
        ->assertStatus(301);
}

public function test_models_generate_urls_from_their_own_neighborhood(): void
{
    $post = Post::factory()->create();
    $business = Business::factory()->create();

    $this->assertStringContainsString('/feed/', $post->canonicalUrl());
    $this->assertStringContainsString('/servicos/', $business->canonicalUrl());
}
```

- [ ] **Step 2: Executar e confirmar a falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodRoutingTest.php
```

Expected: FAIL porque as rotas `neighborhood.*` não existem.

- [ ] **Step 3: Registrar binding composto e aliases**

No `AppServiceProvider::boot()`:

```php
Route::bind('neighborhood', function (string $slug, RouteContract $route): Neighborhood {
    return Neighborhood::query()
        ->where('state_code', strtoupper((string) $route->parameter('state')))
        ->where('city_slug', $route->parameter('city'))
        ->where('slug', $slug)
        ->firstOrFail();
});
```

Em `bootstrap/app.php`:

```php
'neighborhood' => ResolveNeighborhood::class,
'neighborhood.active' => EnsureNeighborhoodIsActive::class,
```

- [ ] **Step 4: Implementar os middlewares**

`ResolveNeighborhood`:

```php
public function handle(Request $request, Closure $next): Response
{
    /** @var Neighborhood $neighborhood */
    $neighborhood = $request->route('neighborhood');

    View::share('currentNeighborhood', $neighborhood);
    session()->put('current_neighborhood_id', $neighborhood->getKey());
    Cookie::queue('last_neighborhood_id', (string) $neighborhood->getKey(), 525_600);

    return $next($request);
}
```

`EnsureNeighborhoodIsActive`:

```php
public function handle(Request $request, Closure $next): Response
{
    abort_unless($request->route('neighborhood')?->is_active, 404);

    return $next($request);
}
```

- [ ] **Step 5: Registrar rotas fixas antes do grupo dinâmico**

Preserve as rotas globais de auth, conta, ranking, sitemap e admin. Adicione:

```php
Route::prefix('{state}/{city}/{neighborhood}')
    ->where([
        'state' => '[a-z]{2}',
        'city' => '[a-z0-9-]+',
        'neighborhood' => '[a-z0-9-]+',
    ])
    ->middleware('neighborhood')
    ->name('neighborhood.')
    ->group(function (): void {
        Route::middleware('neighborhood.active')->group(function (): void {
            Route::get('/', [HomeController::class, 'local'])->name('home');
            Route::get('/busca', [SearchController::class, 'index'])->name('search.index');
            Route::get('/feed', [PostController::class, 'index'])->name('feed.index');
            Route::get('/servicos', [BusinessController::class, 'index'])->name('businesses.index');
            Route::get('/servicos/mapa', [BusinessController::class, 'map'])->name('businesses.map');
            Route::get('/categoria/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
            Route::get('/promocoes', [PromotionController::class, 'index'])->name('promotions.index');
            Route::get('/pulso', [PulsoController::class, 'index'])->name('pulso.index');
            Route::get('/eventos', fn (Neighborhood $neighborhood) => view('events.index', compact('neighborhood')))
                ->name('events.index');
        });

        Route::get('/feed/{post:slug}', [PostController::class, 'show'])
            ->scopeBindings()
            ->name('feed.show');
        Route::get('/servicos/{business:slug}', [BusinessController::class, 'show'])
            ->scopeBindings()
            ->name('businesses.show');
    });
```

Adicione as rotas locais autenticadas de criação, edição, report, claim e
promoção, solicitação de upgrade e analytics de contato sob o mesmo prefixo.
Aplique `neighborhood.active` às gravações e mantenha as rotas de detalhes fora
dele. Aplique `scopeBindings()` somente às rotas com Post ou Business; Category
e Promotion não são relações diretas de Neighborhood e devem validar seu
pertencimento explicitamente no controller.

Nesta Task, mantenha temporariamente as rotas mutáveis antigas para não quebrar
os formulários ainda não migrados. Cada Task funcional remove sua contraparte
legada depois de atualizar os consumidores; a Task 14 confirma que nenhuma rota
mutável sem bairro restou.

Adicione temporariamente `HomeController::local(Neighborhood $neighborhood)`
para renderizar a home atual com `neighborhood` na view. A Task 6 substituirá
suas queries pelas versões isoladas; mantenha `index()` atendendo a raiz antiga
até a Task 9 convertê-la em diretório.

- [ ] **Step 6: Implementar redirects legados**

O controller resolve, nesta ordem, bairro principal ativo, último bairro ativo
do cookie e primeiro ativo:

```php
private function currentNeighborhood(Request $request): Neighborhood
{
    $primary = $request->user()?->primaryNeighborhood;

    if ($primary?->is_active) {
        return $primary;
    }

    return Neighborhood::query()->active()->find($request->cookie('last_neighborhood_id'))
        ?? Neighborhood::query()->active()->orderBy('sort_order')->orderBy('id')->firstOrFail();
}

public function post(Post $post): RedirectResponse
{
    return redirect()->to($post->canonicalUrl(), 301);
}

public function business(Business $business): RedirectResponse
{
    return redirect()->to($business->canonicalUrl(), 301);
}
```

Listagens antigas usam `302` e preservam query string. Detalhes usam `301`.
Mantenha os nomes antigos (`feed.index`, `feed.show`, `businesses.index`,
`businesses.show`) nessas rotas para compatibilidade.

- [ ] **Step 7: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodRoutingTest.php
```

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
rtk git add app/Http/Middleware app/Http/Controllers/LegacyNeighborhoodRedirectController.php \
    app/Providers/AppServiceProvider.php bootstrap/app.php routes/web.php tests/Feature/NeighborhoodRoutingTest.php
rtk git commit -m "feat: route local content through neighborhoods"
```

### Task 4: Associar toda gravação ao bairro da rota

**Files:**
- Create: `tests/Feature/NeighborhoodPublishingTest.php`
- Modify: `app/Actions/CreatePostAction.php`
- Modify: `app/Actions/CreateBusinessAction.php`
- Modify: `app/Actions/UpdateBusinessAction.php`
- Modify: `app/Http/Controllers/PostController.php`
- Modify: `app/Http/Controllers/BusinessController.php`
- Modify: `app/Livewire/Feed/CreatePost.php`
- Modify: `app/Livewire/Business/BusinessForm.php`
- Modify: `app/Http/Requests/StoreBusinessRequest.php`
- Modify: `app/Http/Requests/UpdateBusinessRequest.php`
- Modify: `resources/views/livewire/feed/create-post.blade.php`
- Modify: `resources/views/livewire/business/business-form.blade.php`
- Modify: `tests/Feature/CompositeActionsTest.php`
- Modify: `tests/Feature/PostTest.php`
- Modify: `tests/Feature/BusinessTest.php`
- Modify: `tests/Feature/Feed/EventPostTest.php`
- Modify: `tests/Feature/Feed/PollTest.php`
- Modify: `tests/Feature/Feed/PostImageTest.php`
- Modify: `tests/Feature/Reputation/AwardPointsTest.php`

- [ ] **Step 1: Testar publicação contextual, imutabilidade, duplicidade e limites**

```php
public function test_user_publishes_in_the_visited_neighborhood_not_the_primary_one(): void
{
    $primary = Neighborhood::factory()->create();
    $visited = Neighborhood::factory()->create();
    $user = User::factory()->for($primary, 'primaryNeighborhood')->create();

    Livewire::actingAs($user)
        ->test(CreatePost::class, ['neighborhood' => $visited])
        ->set('title', 'Aviso importante no bairro')
        ->set('body', 'Conteúdo completo para os moradores.')
        ->set('categoryId', Category::factory()->create()->id)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'neighborhood_id' => $visited->id,
    ]);
}

public function test_exact_duplicate_post_is_rejected_for_fifteen_minutes(): void
{
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create([
        'title' => 'Mesmo título',
        'body' => 'Mesmo conteúdo',
        'created_at' => now()->subMinutes(5),
    ]);

    $this->expectException(ValidationException::class);

    app(CreatePostAction::class)->execute(
        $user,
        $post->neighborhood,
        ['category_id' => $post->category_id, 'title' => 'Mesmo título', 'body' => 'Mesmo conteúdo'],
    );
}
```

Inclua testes que tentam enviar cinco posts em dez minutos, três negócios no
mesmo dia e alterar `neighborhood_id` durante edição.

- [ ] **Step 2: Executar e confirmar as falhas**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodPublishingTest.php
```

Expected: FAIL porque as Actions ainda não recebem `Neighborhood`.

- [ ] **Step 3: Alterar `CreatePostAction`**

Assinatura:

```php
public function execute(
    User $user,
    Neighborhood $neighborhood,
    array $data,
    ?TemporaryUploadedFile $image = null,
    ?Carbon $eventStartsAt = null,
    ?Carbon $eventEndsAt = null,
    ?array $pollData = null,
): Post
```

Antes de armazenar imagem:

```php
throw_unless($neighborhood->is_active, ValidationException::withMessages([
    'title' => 'Este bairro não está mais ativo.',
]));

$rateKey = "create-post:{$user->getKey()}";
if (RateLimiter::tooManyAttempts($rateKey, 5)) {
    throw ValidationException::withMessages(['title' => 'Aguarde antes de publicar novamente.']);
}

$duplicateExists = $user->posts()->withTrashed()
    ->where('title', $data['title'])
    ->where('body', $data['body'])
    ->where('created_at', '>=', now()->subMinutes(15))
    ->exists();

if ($duplicateExists) {
    throw ValidationException::withMessages(['title' => 'Esta publicação já foi enviada recentemente.']);
}
```

Grave `'neighborhood_id' => $neighborhood->id` na transação e aplique
`RateLimiter::hit($rateKey, 600)` somente após sucesso. Em
`notifyMerchants()`, filtre negócios por `neighborhood_id` do post.

- [ ] **Step 4: Alterar `CreateBusinessAction` e manter escrita dupla**

Receba `Neighborhood $neighborhood`, valide ativo e aplique:

```php
$rateKey = "create-business:{$user->getKey()}";
if (RateLimiter::tooManyAttempts($rateKey, 3)) {
    throw ValidationException::withMessages([
        'name' => 'Você atingiu o limite diário de cadastros de negócios.',
    ]);
}

// Dentro do create:
'neighborhood_id' => $neighborhood->id,
'neighborhood' => $neighborhood->name,
'city' => $data['city'] ?? $neighborhood->city,
```

Após sucesso, use `RateLimiter::hit($rateKey, 86_400)`.

`UpdateBusinessAction` não recebe nem atualiza `neighborhood_id` ou o campo
legado `neighborhood`.

- [ ] **Step 5: Passar o bairro aos componentes e remover campo textual**

```php
public Neighborhood $neighborhood;

public function mount(Neighborhood $neighborhood): void
{
    $this->neighborhood = $neighborhood;
}
```

Passe o model às Actions, remova o RateLimiter duplicado de `CreatePost`,
remova a propriedade/campo textual `neighborhood` de `BusinessForm` e mostre:

```blade
<div class="rounded-xl border border-[#FD5C3E]/25 bg-[#FD5C3E]/5 px-4 py-3">
    <p class="text-sm font-semibold text-stone-900">
        Publicando em {{ $neighborhood->name }}
    </p>
</div>
```

Remova `neighborhood` das regras de Store/UpdateBusinessRequest.
Passe o bairro às views nos controllers, redirecione sucesso para a URL
canônica ou listagem local e remova as rotas legadas de criação de post e de
criação/edição de negócio somente depois de todos esses consumidores terem sido
atualizados. As rotas antigas de edição/exclusão de post e das demais
interações permanecem até suas respectivas Tasks.

Nos testes que chamam `CreatePostAction` ou `CreateBusinessAction` diretamente,
crie um `Neighborhood` ativo e passe-o como segundo argumento. Isso inclui os
testes compostos, de evento, enquete, imagem e reputação listados nesta Task.
Em `PostTest` e `BusinessTest`, monte todas as URLs de conteúdo e mutação com os
nomes `neighborhood.*` e os parâmetros de `$neighborhood->routeParameters()`;
asserts de redirect para detalhes usam `canonicalUrl()`.

- [ ] **Step 6: Executar todos os testes afetados pelas novas assinaturas**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodPublishingTest.php \
    tests/Feature/PostTest.php \
    tests/Feature/BusinessTest.php \
    tests/Feature/CompositeActionsTest.php \
    tests/Feature/Feed/EventPostTest.php \
    tests/Feature/Feed/PollTest.php \
    tests/Feature/Feed/PostImageTest.php \
    tests/Feature/Reputation/AwardPointsTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Actions app/Http/Controllers/BusinessController.php app/Livewire \
    app/Http/Requests resources/views/livewire tests/Feature
rtk git commit -m "feat: publish content in the selected neighborhood"
```

---

## Entrega 3 — Isolamento funcional e cache

### Task 5: Centralizar cache por bairro e delegar por observers

**Files:**
- Create: `app/Services/NeighborhoodCache.php`
- Create: `app/Observers/PostObserver.php`
- Create: `app/Observers/BusinessObserver.php`
- Create: `app/Observers/PromotionObserver.php`
- Create: `app/Observers/CategoryObserver.php`
- Create: `app/Observers/UserObserver.php`
- Create: `app/Observers/NeighborhoodObserver.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `tests/Feature/HomeCacheTest.php`

- [ ] **Step 1: Reescrever o teste para provar separação e invalidação**

```php
public function test_post_change_invalidates_only_its_neighborhood_cache(): void
{
    $first = Neighborhood::factory()->create();
    $second = Neighborhood::factory()->create();
    $user = User::factory()->create(['neighborhood_id' => $first->id]);
    $category = Category::factory()->create();
    $cache = app(NeighborhoodCache::class);

    $cache->remember($first, NeighborhoodCache::HOME_POSTS, fn () => 'first');
    $cache->remember($second, NeighborhoodCache::HOME_POSTS, fn () => 'second');

    Post::factory()
        ->for($first)
        ->for($user)
        ->for($category)
        ->create();

    $this->assertFalse(Cache::has($cache->key($first, NeighborhoodCache::HOME_POSTS)));
    $this->assertTrue(Cache::has($cache->key($second, NeighborhoodCache::HOME_POSTS)));
}
```

Adicione teste de mudança do bairro principal que invalida bairro antigo e
novo, e de categoria que invalida todos.

- [ ] **Step 2: Executar e confirmar a falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/HomeCacheTest.php
```

Expected: FAIL porque `NeighborhoodCache` não existe.

- [ ] **Step 3: Criar o serviço**

```php
final class NeighborhoodCache
{
    public const HOME_CATEGORIES = 'home:categories';
    public const HOME_POSTS = 'home:posts';
    public const HOME_BUSINESSES = 'home:featured_businesses';
    public const HOME_PROMOTIONS = 'home:promotions';
    public const HOME_EVENTS = 'home:upcoming_events';
    public const HOME_SPONSORED = 'home:sponsored_posts';
    public const HOME_REQUESTS = 'home:requests';
    public const HOME_PULSE_POSTS = 'home:pulso_posts';
    public const HOME_PULSE_RESOLVED = 'home:pulso_resolved';
    public const HOME_STATS = 'home:stats';
    public const PULSE_PREFIX = 'pulso';

    public function key(Neighborhood|int $neighborhood, string $key): string
    {
        $id = $neighborhood instanceof Neighborhood ? $neighborhood->getKey() : $neighborhood;

        return "neighborhood:{$id}:{$key}";
    }

    public function remember(Neighborhood|int $neighborhood, string $key, Closure $callback): mixed
    {
        return Cache::remember($this->key($neighborhood, $key), 300, $callback);
    }

    public function forget(Neighborhood|int $neighborhood): void
    {
        foreach ($this->keys() as $key) {
            Cache::forget($this->key($neighborhood, $key));
        }
    }

    public function forgetAll(): void
    {
        Neighborhood::query()->pluck('id')->each(fn (int $id) => $this->forget($id));
    }
}
```

Liste explicitamente todas as chaves da home e do Pulso em `keys()`.

- [ ] **Step 4: Criar observers finos**

Exemplo:

```php
final class PostObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private NeighborhoodCache $cache) {}

    public function saved(Post $post): void
    {
        $this->cache->forget($post->neighborhood_id);
    }

    public function deleted(Post $post): void
    {
        $this->cache->forget($post->neighborhood_id);
    }

    public function restored(Post $post): void
    {
        $this->cache->forget($post->neighborhood_id);
    }
}
```

Business e Promotion resolvem o bairro próprio/da empresa. User invalida o ID
original e o novo quando `neighborhood_id` muda. Category usa `forgetAll()`.
Neighborhood invalida seu cache e a chave pública `neighborhoods:active`.

- [ ] **Step 5: Registrar observers e retirar o observer genérico**

```php
Post::observe(PostObserver::class);
Business::observe(BusinessObserver::class);
Promotion::observe(PromotionObserver::class);
Category::observe(CategoryObserver::class);
User::observe(UserObserver::class);
Neighborhood::observe(NeighborhoodObserver::class);
```

Remova o registro de `HomeCache` como observer, mas mantenha a classe
temporariamente porque a home raiz legada ainda a usa. Ela será excluída na
Task 9, junto com `HomeController::index()`.

- [ ] **Step 6: Executar o teste**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/HomeCacheTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Services app/Observers app/Providers/AppServiceProvider.php tests/Feature/HomeCacheTest.php
rtk git commit -m "refactor: isolate local caches by neighborhood"
```

### Task 6: Isolar home, feed, busca, categorias e eventos

**Files:**
- Create: `tests/Feature/NeighborhoodContentIsolationTest.php`
- Modify: `app/Http/Controllers/HomeController.php`
- Modify: `app/Http/Controllers/PostController.php`
- Modify: `app/Http/Controllers/SearchController.php`
- Modify: `app/Http/Controllers/CategoryController.php`
- Modify: `app/Livewire/Feed/FeedList.php`
- Modify: `app/Livewire/Events/EventList.php`
- Modify: `app/Livewire/Events/EventCalendar.php`
- Modify: `resources/views/feed/index.blade.php`
- Modify: `resources/views/events/index.blade.php`
- Modify: `resources/views/search/index.blade.php`
- Modify: `resources/views/categories/show.blade.php`
- Modify: `tests/Feature/SearchTest.php`
- Modify: `tests/Feature/Feed/EventPostTest.php`

- [ ] **Step 1: Escrever testes de vazamento**

Crie dois bairros com posts de título único e teste home, feed, busca,
categoria, lista e calendário de eventos:

```php
public function test_local_pages_never_show_content_from_another_neighborhood(): void
{
    $first = Neighborhood::factory()->create();
    $second = Neighborhood::factory()->create();
    $visible = Post::factory()->for($first)->create(['title' => 'Visível no primeiro']);
    $hidden = Post::factory()->for($second)->create(['title' => 'Oculto no segundo']);

    $this->get(route('neighborhood.home', $first->routeParameters()))
        ->assertSee($visible->title)
        ->assertDontSee($hidden->title);

    $this->get(route('neighborhood.search.index', [
        ...$first->routeParameters(),
        'q' => 'segundo',
    ]))->assertDontSee($hidden->title);
}
```

- [ ] **Step 2: Executar e confirmar a falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodContentIsolationTest.php
```

Expected: FAIL porque as queries continuam globais.

- [ ] **Step 3: Aplicar `forNeighborhood()` em todas as queries**

Padrão:

```php
Post::query()
    ->forNeighborhood($neighborhood)
    ->approved();
```

`HomeController::local(Neighborhood $neighborhood, NeighborhoodCache $cache)`
usa o ID em cada `remember()`. Estatísticas de usuários usam
`where('neighborhood_id', $neighborhood->id)`.

`PostController::show()` limita relacionados ao bairro do post.

`SearchController`, `CategoryController`, `FeedList`, `EventList` e
`EventCalendar` recebem `Neighborhood` e filtram antes de qualquer termo,
categoria, data ou ordenação.

Atualize `SearchTest` e `EventPostTest` para acessar as rotas
`neighborhood.search.index` e `neighborhood.events.index` com os parâmetros do
bairro criado no cenário.

- [ ] **Step 4: Remover o toggle textual do feed**

Remova `neighborhoodOnly`, `toggleNeighborhood()` e a consulta por
`user.neighborhood`. Remova o bloco correspondente de
`resources/views/livewire/feed/feed-list.blade.php`.

- [ ] **Step 5: Passar o model aos Livewire components**

```blade
<livewire:feed.feed-list :neighborhood="$neighborhood" />
<livewire:events.event-list :neighborhood="$neighborhood" />
<livewire:events.event-calendar :neighborhood="$neighborhood" />
```

Preserve `q`, categoria e filtros na troca de bairro usando query string.

- [ ] **Step 6: Executar testes focados**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodContentIsolationTest.php \
    tests/Feature/SearchTest.php \
    tests/Feature/Feed/EventPostTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Http/Controllers app/Livewire/Feed app/Livewire/Events resources/views tests/Feature
rtk git commit -m "feat: isolate neighborhood posts and events"
```

### Task 7: Isolar negócios, promoções, Pulso e solicitações

**Files:**
- Modify: `app/Http/Controllers/BusinessController.php`
- Modify: `app/Http/Controllers/PromotionController.php`
- Modify: `app/Http/Controllers/PulsoController.php`
- Modify: `app/Http/Controllers/ProfileController.php`
- Modify: `app/Livewire/Business/BusinessList.php`
- Modify: `app/Actions/CreatePostAction.php`
- Modify: `resources/views/businesses/index.blade.php`
- Modify: `resources/views/promotions/index.blade.php`
- Modify: `resources/views/pulso/index.blade.php`
- Create: `tests/Feature/NeighborhoodBusinessIsolationTest.php`
- Modify: `tests/Feature/Promotion/WeeklyLimitTest.php`

- [ ] **Step 1: Escrever testes para lista, mapa, promoções, Pulso e pedidos**

```php
public function test_business_map_and_promotions_are_scoped_to_the_route_neighborhood(): void
{
    $first = Neighborhood::factory()->create(['latitude' => -22.90, 'longitude' => -43.30]);
    $second = Neighborhood::factory()->create();
    $visible = Business::factory()->create(['neighborhood_id' => $first->id, 'lat' => -22.90, 'lng' => -43.30]);
    $hidden = Business::factory()->create(['neighborhood_id' => $second->id, 'lat' => -22.90, 'lng' => -43.30]);

    $this->getJson(route('neighborhood.businesses.map', [
        ...$first->routeParameters(),
        'north' => -22,
        'south' => -23,
        'east' => -43,
        'west' => -44,
    ]))
        ->assertJsonFragment(['id' => $visible->id])
        ->assertJsonMissing(['id' => $hidden->id]);
}
```

Adicione casos equivalentes para promoções, todas as métricas do Pulso,
solicitações relevantes da conta e destinatários de `NewRequestNotification`.

- [ ] **Step 2: Executar e confirmar falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodBusinessIsolationTest.php
```

Expected: FAIL por vazamento de negócios do segundo bairro.

- [ ] **Step 3: Filtrar as fontes**

- BusinessController e BusinessList: `forNeighborhood($neighborhood)`.
- Mapa: centro vem de `Neighborhood::latitude/longitude`; JSON usa
  `$business->canonicalUrl()`.
- PromotionController: `whereHas('business', fn ($q) => $q->forNeighborhood($neighborhood))`.
- PulsoController: todas as queries de Post e Business recebem o bairro e todas
  as chaves passam por `NeighborhoodCache`.
- Perfil global: pedidos relevantes usam somente os `neighborhood_id` dos
  negócios do usuário.
- `CreatePostAction::notifyMerchants()`: negócio precisa ter categoria e o
  mesmo `neighborhood_id` do post.

Atualize os cenários de limite semanal para usar a rota local de criação de
promoção e o bairro do negócio.

- [ ] **Step 4: Atualizar views e mapa**

Passe o bairro às views, altere títulos para “Serviços em …”, “Promoções em …”
e “Pulso de …”. No JSON do mapa, envie
`'neighborhood' => $business->localNeighborhood->name`; o JavaScript continua
usando `b.neighborhood`.

- [ ] **Step 5: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodBusinessIsolationTest.php \
    tests/Feature/BusinessTest.php \
    tests/Feature/Promotion/WeeklyLimitTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add app/Http/Controllers app/Livewire/Business app/Actions/CreatePostAction.php \
    resources/views tests/Feature
rtk git commit -m "feat: isolate local businesses and pulse"
```

### Task 8: Impedir interação comunitária em bairro inativo

**Files:**
- Create: `tests/Feature/InactiveNeighborhoodTest.php`
- Modify: `app/Policies/PostPolicy.php`
- Modify: `app/Policies/BusinessPolicy.php`
- Modify: `app/Livewire/Feed/CommentSection.php`
- Modify: `app/Livewire/Feed/VoteButtons.php`
- Modify: `app/Livewire/Feed/PollVote.php`
- Modify: `app/Livewire/Feed/InterestButton.php`
- Modify: `app/Livewire/Business/ReviewSection.php`
- Modify: `app/Livewire/Business/PromotionForm.php`
- Modify: `app/Actions/CreatePromotionAction.php`
- Modify: `app/Actions/ClaimBusinessAction.php`
- Modify: `app/Http/Controllers/ClaimBusinessController.php`
- Modify: `resources/views/feed/show.blade.php`
- Modify: `resources/views/businesses/show.blade.php`
- Create: `resources/views/components/inactive-neighborhood-banner.blade.php`
- Modify: `tests/Feature/BusinessReviewTest.php`
- Modify: `tests/Feature/ClaimTest.php`
- Modify: `tests/Feature/Feed/EditPostTest.php`

- [ ] **Step 1: Testar bloqueios e exceções pessoais**

```php
public function test_direct_interactions_are_denied_for_inactive_neighborhood(): void
{
    $post = Post::factory()->for(Neighborhood::factory()->inactive())->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CommentSection::class, ['post' => $post])
        ->set('body', 'Não deve ser salvo')
        ->call('addComment')
        ->assertForbidden();

    $this->assertDatabaseCount('comments', 0);
}

public function test_user_can_still_save_and_report_historical_content(): void
{
    $post = Post::factory()->for(Neighborhood::factory()->inactive())->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(SaveButton::class, ['post' => $post])
        ->call('toggle');

    $this->assertDatabaseHas('post_user_saves', [
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);
}
```

Cubra comentário, resposta, votos em post/comentário/enquete, interesse,
avaliação, promoção e claim.

- [ ] **Step 2: Executar e confirmar a falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/InactiveNeighborhoodTest.php
```

Expected: FAIL porque os componentes ainda gravam normalmente.

- [ ] **Step 3: Adicionar gates de interação**

Policies:

```php
public function interact(User $user, Post $post): bool
{
    return $post->acceptsCommunityInteractions();
}

public function interact(User $user, Business $business): bool
{
    return $business->acceptsCommunityInteractions();
}
```

Antes de qualquer mutação comunitária:

```php
Gate::authorize('interact', $this->post);
// ou
Gate::authorize('interact', $this->business);
```

`CreatePromotionAction` também lança `ValidationException` quando o negócio
está inativo, protegendo chamadas internas. `ClaimBusinessAction` faz a mesma
validação antes de abrir uma solicitação. As Policies de atualização e exclusão
de Post e Business negam mutações de usuários comuns em bairro inativo; ações
administrativas de moderação continuam disponíveis. Aplique o gate também a
edição/exclusão de comentários e avaliações, não apenas à criação.

Atualize `BusinessReviewTest`, `ClaimTest` e `EditPostTest` para usar URLs
canônicas e rotas locais. Não adicione gate em FavoriteButton, SaveButton ou
ReportController.

Depois de atualizar os formulários, remova as rotas legadas de edição de post,
claim e criação de promoção. Mantenha denúncia, analytics, exclusões e os
demais endpoints antigos até a varredura final da Task 14.

- [ ] **Step 4: Renderizar detalhes históricos**

No topo das páginas:

```blade
@unless($post->neighborhood->is_active)
    <x-inactive-neighborhood-banner :neighborhood="$post->neighborhood" />
    @push('head')
        <meta name="robots" content="noindex,follow">
    @endpush
@endunless
```

O componente mostra “Este bairro não está mais ativo”. Oculte controles
comunitários quando `acceptsCommunityInteractions()` for falso, mantendo salvar,
favoritar e denunciar.

- [ ] **Step 5: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/InactiveNeighborhoodTest.php \
    tests/Feature/CommentTest.php \
    tests/Feature/VoteTest.php \
    tests/Feature/BusinessReviewTest.php \
    tests/Feature/ClaimTest.php \
    tests/Feature/Feed/EditPostTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add app/Policies app/Livewire app/Actions app/Http/Controllers \
    resources/views tests/Feature/InactiveNeighborhoodTest.php
rtk git commit -m "feat: preserve inactive neighborhoods as read only"
```

---

## Entrega 4 — Experiência, cadastro e bairro principal

### Task 9: Implementar diretório, bairro principal e onboarding

**Files:**
- Create: `app/Actions/UpdatePrimaryNeighborhoodAction.php`
- Create: `app/Http/Controllers/NeighborhoodSelectionController.php`
- Create: `app/Http/Requests/UpdatePrimaryNeighborhoodRequest.php`
- Create: `app/Http/Middleware/EnsurePrimaryNeighborhood.php`
- Create: `resources/views/neighborhoods/index.blade.php`
- Create: `tests/Feature/NeighborhoodSelectionTest.php`
- Modify: `app/Http/Controllers/HomeController.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Delete: `app/Services/HomeCache.php`

- [ ] **Step 1: Testar raiz, último bairro e alteração explícita**

```php
public function test_guest_root_lists_active_neighborhoods_and_continue_shortcut(): void
{
    $active = Neighborhood::factory()->create(['name' => 'Engenho da Rainha']);
    $inactive = Neighborhood::factory()->inactive()->create(['name' => 'Inativo']);

    $this->withCookie('last_neighborhood_id', (string) $active->id)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Engenho da Rainha')
        ->assertSee('Continuar em Engenho da Rainha')
        ->assertDontSee('Inativo');
}

public function test_switching_route_does_not_change_primary_neighborhood(): void
{
    $primary = Neighborhood::factory()->create();
    $visited = Neighborhood::factory()->create();
    $user = User::factory()->create(['neighborhood_id' => $primary->id]);

    $this->actingAs($user)
        ->get(route('neighborhood.home', $visited->routeParameters()))
        ->assertOk();

    $this->assertSame($primary->id, $user->refresh()->neighborhood_id);
}
```

Adicione testes de PATCH próprio, usuário sem bairro, bairro inativo e usuário
autenticado acessando `/`.

- [ ] **Step 2: Executar e confirmar falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodSelectionTest.php
```

Expected: FAIL porque a raiz ainda renderiza a home local.

- [ ] **Step 3: Implementar raiz e seleção**

`HomeController::directory()`:

```php
public function directory(Request $request): View|RedirectResponse
{
    $primary = $request->user()?->primaryNeighborhood;

    if ($primary?->is_active) {
        return redirect()->route('neighborhood.home', $primary->routeParameters());
    }

    $neighborhoods = Neighborhood::query()
        ->active()
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();
    $lastNeighborhood = $neighborhoods->firstWhere(
        'id',
        (int) $request->cookie('last_neighborhood_id'),
    );

    return view('neighborhoods.index', compact('neighborhoods', 'lastNeighborhood'));
}
```

Troque a rota `/` para `[HomeController::class, 'directory']`, remova o método
global antigo `index()`, exclua o `HomeCache` legado que ficou apenas para essa
transição e mantenha `local()` como a única home de conteúdo.

`UpdatePrimaryNeighborhoodAction` valida ativo e atualiza
`neighborhood_id` mais o campo legado `neighborhood`.

Registre fora do middleware `primary-neighborhood`, mas dentro de `auth`:

```php
Route::get('/escolher-bairro', [NeighborhoodSelectionController::class, 'create'])
    ->name('neighborhoods.select');
Route::patch('/meu-bairro', [NeighborhoodSelectionController::class, 'update'])
    ->name('neighborhoods.update');
```

- [ ] **Step 4: Implementar middleware de onboarding**

```php
public function handle(Request $request, Closure $next): Response
{
    if (! $request->user()?->primaryNeighborhood?->is_active) {
        return redirect()->route('neighborhoods.select');
    }

    return $next($request);
}
```

Registre `primary-neighborhood` e aplique às rotas autenticadas, exceto
seleção, atualização do bairro e logout.

- [ ] **Step 5: Criar tela acessível de diretório**

Cards exibem nome, cidade e UF, usam Heroicons e apontam para
`neighborhood.home`. Para usuário sem bairro, o botão POST/PATCH define o
principal. Visitante vê apenas links e o atalho de continuidade.

- [ ] **Step 6: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodSelectionTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Actions app/Http/Controllers app/Http/Requests app/Http/Middleware \
    app/Services/HomeCache.php bootstrap/app.php routes/web.php \
    resources/views/neighborhoods tests/Feature
rtk git commit -m "feat: select a primary neighborhood"
```

### Task 10: Integrar cadastro tradicional e Google

**Files:**
- Create: `app/Http/Requests/Auth/RegisterUserRequest.php`
- Modify: `app/Http/Controllers/Auth/RegisteredUserController.php`
- Modify: `app/Http/Controllers/Auth/GoogleAuthController.php`
- Modify: `app/Actions/HandleGoogleAuthentication.php`
- Modify: `resources/views/auth/register.blade.php`
- Modify: `tests/Feature/Auth/RegistrationTest.php`
- Modify: `tests/Feature/Auth/GoogleAuthTest.php`

- [ ] **Step 1: Testar cadastro contextual e callback com bairro desativado**

```php
public function test_registration_uses_the_validated_current_neighborhood(): void
{
    $neighborhood = Neighborhood::factory()->create();

    $this->withSession(['current_neighborhood_id' => $neighborhood->id])
        ->post(route('register'), [
            'name' => 'Novo morador',
            'email' => 'novo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'neighborhood_id' => $neighborhood->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'novo@example.com',
        'neighborhood_id' => $neighborhood->id,
        'neighborhood' => $neighborhood->name,
    ]);
}

public function test_google_callback_revalidates_a_neighborhood_deactivated_during_oauth(): void
{
    $neighborhood = Neighborhood::factory()->inactive()->create();
    Socialite::shouldReceive('driver->user')->andReturn($this->createSocialiteUser());

    $this->withSession(['oauth_neighborhood_id' => $neighborhood->id])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('neighborhoods.select'));
}
```

- [ ] **Step 2: Executar e confirmar falhas**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/Auth/RegistrationTest.php \
    tests/Feature/Auth/GoogleAuthTest.php
```

Expected: FAIL nas novas expectativas de bairro.

- [ ] **Step 3: Criar `RegisterUserRequest`**

Regras:

```php
public function authorize(): bool
{
    return true;
}

// rules()
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
'password' => ['required', 'confirmed', Rules\Password::defaults()],
'neighborhood_id' => [
    'required',
    'integer',
    Rule::exists('neighborhoods', 'id')->where('is_active', true),
],
```

Controller grava FK e nome legado do model validado.

- [ ] **Step 4: Preservar contexto OAuth**

No redirect, copie apenas o ID já validado da sessão:

```php
$neighborhood = Neighborhood::query()
    ->active()
    ->find(session('current_neighborhood_id'));

session()->put('oauth_neighborhood_id', $neighborhood?->id);
```

O Socialite continua gerando e validando `state`. No callback, reler
`oauth_neighborhood_id`, buscar novamente com `active()` e nunca aceitar bairro
da query string. Passe o model válido ao `HandleGoogleAuthentication`; ele só
preenche bairro para usuário recém-criado. Se o usuário final continuar sem
bairro ativo, redirecione a `neighborhoods.select`.

Altere a assinatura da Action para:

```php
public function execute(
    SocialiteUser $googleUser,
    ?Neighborhood $neighborhood = null,
): User
```

Ao criar um usuário, grave `neighborhood_id` e o nome legado quando houver
bairro válido. Ao localizar uma conta existente, não sobrescreva o bairro
principal escolhido pelo usuário.

Mantenha a validação atual de `url.intended` para caminhos iniciados por `/` e
não por `//`.

- [ ] **Step 5: Atualizar formulário**

Carregue bairros ativos. Quando houver contexto, deixe-o selecionado e mostre a
confirmação; sem contexto, exiba select obrigatório.

- [ ] **Step 6: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/Auth/RegistrationTest.php \
    tests/Feature/Auth/GoogleAuthTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Http/Requests/Auth app/Http/Controllers/Auth app/Actions/HandleGoogleAuthentication.php \
    resources/views/auth/register.blade.php tests/Feature/Auth
rtk git commit -m "feat: preserve neighborhood through authentication"
```

### Task 11: Exibir e trocar bairro na navegação

**Files:**
- Create: `resources/views/components/neighborhood-switcher.blade.php`
- Create: `tests/Feature/NeighborhoodNavigationTest.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `resources/views/layouts/navigation.blade.php`
- Modify: `resources/views/home/index.blade.php`
- Modify: `resources/views/feed/index.blade.php`
- Modify: `resources/views/businesses/index.blade.php`
- Modify: `resources/views/promotions/index.blade.php`
- Modify: `resources/views/events/index.blade.php`
- Modify: `resources/views/search/index.blade.php`
- Modify: `resources/views/pulso/index.blade.php`
- Modify: `resources/views/profile/account.blade.php`
- Modify: `resources/views/profile/edit.blade.php`
- Modify: `resources/views/users/show.blade.php`
- Modify: `resources/views/users/ranking.blade.php`
- Modify: `app/Http/Requests/ProfileUpdateRequest.php`
- Modify: `app/Actions/UpdateProfileAction.php`

- [ ] **Step 1: Testar presença desktop/mobile e atualização de perfil**

```php
public function test_local_navigation_shows_the_current_neighborhood_twice_for_responsive_layouts(): void
{
    $neighborhood = Neighborhood::factory()->create(['name' => 'Engenho da Rainha']);

    $this->get(route('neighborhood.home', $neighborhood->routeParameters()))
        ->assertOk()
        ->assertSee('Engenho da Rainha', false)
        ->assertSee('data-neighborhood-switcher-desktop', false)
        ->assertSee('data-neighborhood-switcher-mobile', false);
}
```

- [ ] **Step 2: Executar e confirmar falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/NeighborhoodNavigationTest.php
```

Expected: FAIL porque o seletor não existe.

- [ ] **Step 3: Compartilhar lista ativa com a navbar**

Use um view composer no `AppServiceProvider`:

```php
View::composer('layouts.navigation', function (ViewContract $view): void {
    $view->with('navigationNeighborhoods', Cache::remember(
        'neighborhoods:active',
        300,
        fn () => Neighborhood::query()->active()->orderBy('sort_order')->orderBy('id')->get(),
    ));
});
```

O bairro exibido é `$currentNeighborhood` nas rotas locais ou o
`primaryNeighborhood` nas páginas globais.

- [ ] **Step 4: Criar o componente do seletor**

Use Alpine para abrir/fechar, Heroicon `map-pin`, foco visível e lista de links.
Links de listagem preservam `q`, `categoryId` e `sortBy`; detalhes e formulários
vão à home escolhida. Para usuário autenticado, um formulário PATCH separado
mostra “Tornar meu bairro principal”.

Renderize:

```blade
<div data-neighborhood-switcher-desktop class="hidden lg:block">
    <x-neighborhood-switcher :current="$displayNeighborhood" :neighborhoods="$navigationNeighborhoods" />
</div>

<div data-neighborhood-switcher-mobile class="border-t border-stone-200 lg:hidden">
    <x-neighborhood-switcher :current="$displayNeighborhood" :neighborhoods="$navigationNeighborhoods" mobile />
</div>
```

Não mova o sino mobile nem o menu.

- [ ] **Step 5: Contextualizar títulos e listas globais**

Use “Últimas notícias de …”, “Serviços em …”, “Eventos em …”, “Pulso de …” e
“Buscando em …”. Favoritos, salvos, comentários, ranking e perfil exibem o nome
do relacionamento, sem sugerir que os pontos foram ganhos naquele bairro.

Troque o campo textual de perfil por `neighborhood_id` com
`Rule::exists(...)->where('is_active', true)` e mantenha escrita dupla em
`UpdateProfileAction`:

```php
if (array_key_exists('neighborhood_id', $data)) {
    $neighborhood = Neighborhood::query()->active()->findOrFail($data['neighborhood_id']);
    $data['neighborhood'] = $neighborhood->name;
}
```

- [ ] **Step 6: Executar testes e build**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodNavigationTest.php \
    tests/Feature/ProfileTest.php
rtk vendor/bin/sail npm run build
```

Expected: testes PASS e build concluído.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Providers/AppServiceProvider.php app/Http/Requests app/Actions/UpdateProfileAction.php \
    resources/views tests/Feature/NeighborhoodNavigationTest.php
rtk git commit -m "feat: surface the active neighborhood across the UI"
```

---

## Entrega 5 — Administração, integrações e endurecimento

### Task 12: Criar administração de bairros com concorrência segura

**Files:**
- Create: `app/Actions/SaveNeighborhoodAction.php`
- Create: `app/Actions/SetNeighborhoodStatusAction.php`
- Create: `app/Livewire/Admin/NeighborhoodManager.php`
- Create: `resources/views/livewire/admin/neighborhood-manager.blade.php`
- Create: `tests/Feature/Admin/NeighborhoodManagementTest.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/navigation.blade.php`
- Delete: `app/Livewire/Admin/AppSettings.php`
- Delete: `resources/views/livewire/admin/app-settings.blade.php`

- [ ] **Step 1: Testar acesso, validação, slug imutável e corrida**

```php
public function test_admin_cannot_deactivate_the_last_active_neighborhood(): void
{
    $admin = User::factory()->create(['is_admin' => true]);
    $neighborhood = Neighborhood::factory()->create();

    Livewire::actingAs($admin)
        ->test(NeighborhoodManager::class)
        ->call('toggleActive', $neighborhood->id)
        ->assertHasErrors('status');

    $this->assertTrue($neighborhood->refresh()->is_active);
}
```

Adicione teste com duas conexões/transações tentando desativar os dois últimos
bairros e confirme que ao menos um permanece ativo. Teste que usuário comum
recebe `403` e que UF/cidade/slugs não mudam após existir conteúdo.

- [ ] **Step 2: Executar e confirmar falha**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/Admin/NeighborhoodManagementTest.php
```

Expected: FAIL porque o componente não existe.

- [ ] **Step 3: Implementar Actions**

`SaveNeighborhoodAction` normaliza:

```php
$data['state_code'] = strtoupper($data['state_code']);
$data['slug'] = Str::slug($data['slug'] ?: $data['name']);
$data['city_slug'] = Str::slug($data['city_slug'] ?: $data['city']);
```

Valide `name` e `city` como strings obrigatórias, `state_code` com exatamente
dois caracteres, latitude entre `-90` e `90`, longitude entre `-180` e `180`,
`sort_order` inteiro não negativo e `is_active` booleano. Valide unicidade
composta de `state_code`, `city_slug` e `slug`. Se o bairro tiver posts ou
negócios, preserve `state_code`, `city`, `city_slug` e `slug`.

`SetNeighborhoodStatusAction`:

```php
return DB::transaction(function () use ($neighborhood, $active): Neighborhood {
    $activeIds = Neighborhood::query()
        ->active()
        ->orderBy('id')
        ->lockForUpdate()
        ->pluck('id');
    $locked = Neighborhood::query()->lockForUpdate()->findOrFail($neighborhood->id);

    if (! $active && $locked->is_active && $activeIds->count() <= 1) {
        throw ValidationException::withMessages([
            'status' => 'O último bairro ativo não pode ser desativado.',
        ]);
    }

    $locked->update(['is_active' => $active]);

    return $locked;
});
```

- [ ] **Step 4: Implementar Livewire e substituir AppSettings**

Campos: nome, slug, cidade, city_slug, UF, latitude, longitude, sort_order e
ativo. Use `SaveNeighborhoodAction` e `SetNeighborhoodStatusAction`. Não
ofereça exclusão.

Rota:

```php
Route::get('/bairros', NeighborhoodManager::class)->name('neighborhoods');
```

Remova `/admin/configuracoes` e seus dois arquivos antigos. Adicione “Bairros”
ao menu admin.

- [ ] **Step 5: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact tests/Feature/Admin/NeighborhoodManagementTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add app/Actions app/Livewire/Admin resources/views/livewire/admin \
    resources/views/layouts/navigation.blade.php routes/web.php tests/Feature/Admin
rtk git commit -m "feat: manage active neighborhoods"
```

### Task 13: Adaptar Google Places e comando de auditoria

**Files:**
- Create: `app/Console/Commands/AuditNeighborhoodAssignments.php`
- Create: `tests/Feature/NeighborhoodAuditCommandTest.php`
- Modify: `app/Livewire/Admin/GooglePlacesImport.php`
- Modify: `resources/views/livewire/admin/google-places-import.blade.php`
- Modify: `app/Actions/ImportBusinessFromGoogleAction.php`
- Modify: `app/Console/Commands/ImportBusinessesFromGoogle.php`
- Modify: `tests/Feature/Admin/GooglePlacesImportTest.php`
- Modify: `tests/Feature/EnrichBusinessFromGoogleTest.php`

- [ ] **Step 1: Testar importação contextual e auditoria**

```php
public function test_google_import_assigns_the_selected_neighborhood(): void
{
    $neighborhood = Neighborhood::factory()->create([
        'latitude' => -22.90,
        'longitude' => -43.30,
    ]);

    $business = app(ImportBusinessFromGoogleAction::class)->execute(
        place: $this->placePayload(),
        neighborhood: $neighborhood,
        fallbackCategoryId: Category::factory()->create()->id,
    );

    $this->assertSame($neighborhood->id, $business->neighborhood_id);
    $this->assertSame($neighborhood->name, $business->neighborhood);
}

public function test_audit_command_fails_when_required_assignments_are_missing(): void
{
    Post::factory()->create(['neighborhood_id' => null]);

    $this->artisan('neighborhoods:audit')
        ->expectsOutputToContain('posts sem bairro: 1')
        ->assertFailed();
}
```

Em `EnrichBusinessFromGoogleTest`, instancie
`new EnrichBusinessFromGoogle($business->id)` antes de atualizar o
`neighborhood_id`, execute o job depois da atualização e confirme que o negócio
continua associado ao bairro. Isso representa um payload antigo processado
depois do backfill.

- [ ] **Step 2: Executar e confirmar falha**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodAuditCommandTest.php \
    tests/Feature/Admin/GooglePlacesImportTest.php
```

Expected: FAIL porque Action e comando ainda usam texto.

- [ ] **Step 3: Alterar importação**

`GooglePlacesImport` usa `public int $neighborhoodId`, carrega bairros ativos e,
ao selecionar, preenche lat/lng. `ImportBusinessFromGoogleAction::execute()`
recebe `Neighborhood $neighborhood` e grava FK, nome legado e cidade.

O comando CLI muda `--neighborhood=` para um ID, busca com `active()` e usa as
coordenadas do model quando `--lat/--lng` não forem fornecidos.

- [ ] **Step 4: Criar auditoria**

```php
public function handle(): int
{
    $users = User::query()->whereNull('neighborhood_id')->count();
    $posts = Post::query()->withTrashed()->whereNull('neighborhood_id')->count();
    $businesses = Business::query()->withTrashed()->whereNull('neighborhood_id')->count();

    $this->line("users sem bairro: {$users}");
    $this->line("posts sem bairro: {$posts}");
    $this->line("businesses sem bairro: {$businesses}");

    return $posts === 0 && $businesses === 0 ? self::SUCCESS : self::FAILURE;
}
```

Usuários nullable não falham o checkpoint; posts e negócios falham.

- [ ] **Step 5: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodAuditCommandTest.php \
    tests/Feature/Admin/GooglePlacesImportTest.php \
    tests/Feature/EnrichBusinessFromGoogleTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add app/Console app/Livewire/Admin app/Actions/ImportBusinessFromGoogleAction.php \
    resources/views/livewire/admin tests/Feature
rtk git commit -m "feat: scope business imports to neighborhoods"
```

### Task 14: Atualizar URLs, notificações, SEO e sitemap

**Files:**
- Modify: `app/Notifications/CommentNotification.php`
- Modify: `app/Notifications/CommentVoteNotification.php`
- Modify: `app/Notifications/PostVoteNotification.php`
- Modify: `app/Notifications/InterestNotification.php`
- Modify: `app/Notifications/NewRequestNotification.php`
- Modify: `app/Notifications/PlanUpgradeApprovedNotification.php`
- Modify: `app/Actions/ClaimBusinessAction.php`
- Modify: `app/Http/Controllers/Admin/ModerationController.php`
- Modify: `app/Http/Controllers/SitemapController.php`
- Modify: `app/Livewire/Events/EventCalendar.php`
- Modify: `app/Livewire/Feed/EditPost.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/sitemap.blade.php`
- Modify: `resources/views/components/post-card.blade.php`
- Modify: `resources/views/components/business-card.blade.php`
- Modify: `resources/views/components/featured-benefits.blade.php`
- Modify: `resources/views/home/index.blade.php`
- Modify: `resources/views/search/index.blade.php`
- Modify: `resources/views/pulso/index.blade.php`
- Modify: `resources/views/feed/show.blade.php`
- Modify: `resources/views/feed/edit.blade.php`
- Modify: `resources/views/businesses/show.blade.php`
- Modify: `resources/views/livewire/events/event-list.blade.php`
- Modify: `resources/views/livewire/events/event-calendar.blade.php`
- Modify: `resources/views/livewire/feed/edit-post.blade.php`
- Modify: `resources/views/livewire/business/business-form.blade.php`
- Modify: `resources/views/admin/moderation/_item.blade.php`
- Modify: `resources/views/admin/moderation/index.blade.php`
- Modify: `resources/views/admin/sponsored-posts.blade.php`
- Modify: `tests/Feature/SitemapTest.php`
- Modify: `tests/Feature/QueuedNotificationsTest.php`
- Modify: `tests/Feature/WebPushNotificationTest.php`
- Modify: `tests/Feature/ModerationTest.php`
- Modify: `tests/Feature/SmokeTest.php`

- [ ] **Step 1: Escrever testes de URL enfileirada e sitemap**

```php
public function test_notification_resolves_the_entity_canonical_url(): void
{
    $post = Post::factory()->create();
    $notification = new CommentNotification(
        Comment::factory()->for($post)->create(),
    );

    $this->assertSame(
        $post->canonicalUrl(),
        $notification->toArray($post->user)['url'],
    );
}

public function test_sitemap_contains_only_active_neighborhood_content(): void
{
    $activePost = Post::factory()->for(Neighborhood::factory())->create();
    $inactivePost = Post::factory()->for(Neighborhood::factory()->inactive())->create();

    $this->get(route('sitemap'))
        ->assertSee($activePost->canonicalUrl(), false)
        ->assertDontSee($inactivePost->canonicalUrl(), false);
}
```

- [ ] **Step 2: Executar e confirmar falhas**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/SitemapTest.php \
    tests/Feature/QueuedNotificationsTest.php \
    tests/Feature/WebPushNotificationTest.php
```

Expected: FAIL porque URLs antigas continuam sendo geradas.

- [ ] **Step 3: Usar helpers canônicos em todos os consumidores**

Posts:

```php
$post->canonicalUrl();
$post->canonicalUrl(absolute: false);
```

Negócios:

```php
$business->canonicalUrl();
$business->canonicalUrl(absolute: false);
```

Jobs e notifications mantêm o model/ID serializado e calculam a URL apenas em
`toArray()`, `toMail()` ou `toWebPush()`, nunca no construtor nem por sessão.

- [ ] **Step 4: Atualizar cards e views**

Substitua todo `route('feed.show', ...)` e `route('businesses.show', ...)` por
helpers canônicos. Para listagens locais, use
`route('neighborhood.<name>', $neighborhood->routeParameters())`.
Em `businesses/show.blade.php`, gere a URL local de analytics no servidor e
passe-a ao JavaScript; não monte `/negocio/{id}/rastrear/...` manualmente.

Atualize os formulários restantes e remova de `routes/web.php` todas as rotas
mutáveis legadas sem os parâmetros `state`, `city` e `neighborhood`. Preserve
globais somente autenticação, conta, notificações, ranking, sitemap e admin.
Denúncia de detalhe histórico permanece na rota local, mas fora do middleware
`neighborhood.active`.

Atualize `pwa-install-safe` no layout para os nomes `neighborhood.*`. Nas
páginas locais, envie `<link rel="canonical">` e `og:url` com a URL do bairro ou
da entidade; detalhes históricos inativos mantêm `noindex,follow`.

Use:

```bash
rtk rg -n "route\\('(feed|businesses|promotions|events|pulso|search|categories|report|business\\.track)" \
    app resources/views tests
rtk rg -n "/negocio/.*/rastrear|/feed/|/servicos/" resources/views app
```

O comando final deve mostrar apenas redirects legados, rotas globais
deliberadas e testes de compatibilidade.

- [ ] **Step 5: Atualizar sitemap**

Carregue `neighborhood`/`localNeighborhood`, filtre relacionamento ativo e gere
home, feed, serviços, promoções, eventos, Pulso, categorias, posts e negócios
para cada bairro ativo. Não gere URL de bairro inativo.

- [ ] **Step 6: Executar testes**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/SitemapTest.php \
    tests/Feature/QueuedNotificationsTest.php \
    tests/Feature/WebPushNotificationTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
rtk git add app/Notifications app/Actions app/Http/Controllers app/Livewire \
    resources/views tests/Feature
rtk git commit -m "feat: generate canonical neighborhood URLs"
```

### Task 15: Verificação final e checklist de deploy expand

- [ ] **Step 1: Limpar caches e executar migrations no Sail**

```bash
rtk vendor/bin/sail php artisan optimize:clear
rtk vendor/bin/sail php artisan migrate
rtk vendor/bin/sail php artisan neighborhoods:audit
```

Expected: migrations concluídas e zero posts/negócios órfãos.

- [ ] **Step 2: Executar testes de multibairro juntos**

```bash
rtk vendor/bin/sail php artisan test --compact \
    tests/Feature/NeighborhoodMigrationTest.php \
    tests/Feature/NeighborhoodModelTest.php \
    tests/Feature/NeighborhoodRoutingTest.php \
    tests/Feature/NeighborhoodPublishingTest.php \
    tests/Feature/NeighborhoodContentIsolationTest.php \
    tests/Feature/NeighborhoodBusinessIsolationTest.php \
    tests/Feature/InactiveNeighborhoodTest.php \
    tests/Feature/NeighborhoodSelectionTest.php \
    tests/Feature/NeighborhoodNavigationTest.php \
    tests/Feature/Admin/NeighborhoodManagementTest.php \
    tests/Feature/NeighborhoodAuditCommandTest.php
```

Expected: PASS.

- [ ] **Step 3: Executar suíte completa**

```bash
rtk vendor/bin/sail php artisan test --compact
```

Expected: todos os testes passam.

- [ ] **Step 4: Executar formatter e build**

```bash
rtk vendor/bin/sail vendor/bin/pint --test
rtk vendor/bin/sail npm run build
```

Expected: Pint sem alterações e build Vite concluído.

- [ ] **Step 5: Verificar rotas e referências legadas**

```bash
rtk vendor/bin/sail php artisan route:list
rtk rg -n "Setting::get\\('neighborhood_|->where\\('neighborhood'|neighborhoodOnly" app resources/views
```

Expected:

- rotas fixas aparecem antes do grupo local;
- URLs locais têm as três constraints;
- nenhum filtro funcional usa texto livre;
- apenas escrita dupla e migration referenciam campos legados.

- [ ] **Step 6: Reexecutar a verificação após o formatter**

```bash
rtk vendor/bin/sail php artisan test --compact
rtk vendor/bin/sail vendor/bin/pint --test
rtk vendor/bin/sail npm run build
```

Expected: suíte completa, Pint e build passam no estado exato que será
commitado.

- [ ] **Step 7: Commit final de verificação, somente se houver ajustes**

```bash
rtk git add -A
rtk git commit -m "test: verify multibairro rollout"
```

## Checklist de produção após merge

No primeiro deploy, ainda com somente Engenho da Rainha ativo:

```bash
cd /var/www/falavizin
docker compose --env-file .env -f compose.production.yaml pull
docker compose --env-file .env -f compose.production.yaml up -d
docker compose --env-file .env -f compose.production.yaml exec -u www-data web php artisan migrate --force
docker compose --env-file .env -f compose.production.yaml exec -u www-data web php artisan neighborhoods:audit
docker compose --env-file .env -f compose.production.yaml exec -u www-data web php artisan optimize
```

Não cadastre o segundo bairro e não crie a migration de contrato se a auditoria
falhar. Depois de um deploy estável, abra um novo plano para:

1. tornar `posts.neighborhood_id` e `businesses.neighborhood_id` obrigatórios;
2. manter escrita dupla por mais um deploy;
3. tornar o legado nullable e parar a escrita dupla;
4. remover os campos legados em migration posterior.

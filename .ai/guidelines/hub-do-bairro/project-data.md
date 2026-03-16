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

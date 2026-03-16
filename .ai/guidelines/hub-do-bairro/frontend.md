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

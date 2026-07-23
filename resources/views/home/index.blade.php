<x-app-layout>

@push('head')
<style>
    /* ── Hero animations ─────────────────────── */
    @keyframes heroUp   { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:translateY(0); } }
    @keyframes signal   { 0% { transform:scale(.8); opacity:.7; } 100% { transform:scale(2.6); opacity:0; } }
    @keyframes fadeRule { from { width:0; } to { width:2.5rem; } }
    @keyframes scrollIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes tickerSlide { 0% { transform:translateX(0); } 100% { transform:translateX(-50%); } }

    .h-up1 { animation: heroUp .8s cubic-bezier(.16,1,.3,1) .05s both; }
    .h-up2 { animation: heroUp .8s cubic-bezier(.16,1,.3,1) .18s both; }
    .h-up3 { animation: heroUp .8s cubic-bezier(.16,1,.3,1) .30s both; }
    .h-up4 { animation: heroUp .8s cubic-bezier(.16,1,.3,1) .44s both; }

    .sig-ring {
        position:absolute; border-radius:50%;
        border:1.5px solid rgba(217,119,6,.45);
        animation: signal 3s ease-out infinite;
    }

    /* Scroll-triggered reveal */
    [data-reveal] {
        opacity:0; transform:translateY(18px);
        transition: opacity .55s cubic-bezier(.16,1,.3,1), transform .55s cubic-bezier(.16,1,.3,1);
    }
    [data-reveal].on { opacity:1; transform:translateY(0); }
    [data-reveal-d2] { transition-delay:.08s; }
    [data-reveal-d3] { transition-delay:.16s; }
    [data-reveal-d4] { transition-delay:.24s; }

    /* Section label style */
    .sec-eyebrow {
        display:inline-flex; align-items:center; gap:.6rem;
        font-size:.6875rem; font-weight:700; letter-spacing:.1em;
        text-transform:uppercase; color:#92400e; margin-bottom:.5rem;
    }
    .sec-eyebrow::before {
        content:''; display:inline-block; height:2px; border-radius:2px;
        background:#d97706; animation: fadeRule .5s .3s both;
        flex-shrink:0;
    }

    /* Category pills */
    .cat-pill { transition: box-shadow .15s, border-color .15s, background .15s, color .15s; }
    .cat-pill:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); border-color:#fcd34d; }

    /* Hover lift on cards */
    .lift { transition: transform .2s cubic-bezier(.16,1,.3,1), box-shadow .2s; }
    .lift:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,.1); }

    /* Promo card hover */
    .promo-card { transition: transform .15s, box-shadow .15s; }
    .promo-card:hover { transform:translateX(3px); }

    /* Biz list item */
    .biz-row { transition: background .15s, padding-left .15s; }
    .biz-row:hover { background:#fffbeb; padding-left:1rem; }

    /* Diagonal stripe texture for hero */
    .stripe-tex {
        background-image: repeating-linear-gradient(
            -55deg,
            rgba(255,255,255,.018) 0px, rgba(255,255,255,.018) 1px,
            transparent 1px, transparent 10px
        );
    }

    /* Ticker */
    .ticker-track { display:flex; animation: tickerSlide 28s linear infinite; }
    .ticker-track:hover { animation-play-state:paused; }

    /* Pulse dot for Pulso widget */
    @keyframes pulseDot { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.5; transform:scale(1.5); } }
    .pulse-dot { animation: pulseDot 2s ease-in-out infinite; }
</style>
@endpush

{{-- ════════════════════════════════════════════
     HERO
════════════════════════════════════════════ --}}
<section class="relative overflow-hidden stripe-tex"
         style="background:linear-gradient(130deg,#1c1917 0%,#27180a 50%,#1a120a 100%);">

    {{-- Glow sweeps --}}
    <div style="position:absolute;inset:0;pointer-events:none;overflow:hidden;">
        <div style="position:absolute;top:-30%;right:-10%;width:65%;height:130%;background:radial-gradient(ellipse,rgba(217,119,6,.16) 0%,transparent 60%);"></div>
        <div style="position:absolute;bottom:-20%;left:-5%;width:50%;height:80%;background:radial-gradient(ellipse,rgba(120,53,15,.22) 0%,transparent 65%);"></div>
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(to bottom,transparent 60%,rgba(28,25,23,.9) 100%);"></div>
    </div>

    {{-- Signal decoration (desktop) --}}
    <div class="hidden lg:block" style="position:absolute;right:9%;top:50%;transform:translateY(-50%);width:240px;height:240px;pointer-events:none;">
        <div style="position:relative;width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
            <div class="sig-ring" style="width:68px;height:68px;animation-delay:0s;"></div>
            <div class="sig-ring" style="width:68px;height:68px;animation-delay:1.1s;"></div>
            <div class="sig-ring" style="width:68px;height:68px;animation-delay:2.2s;"></div>
            <div style="position:relative;z-index:1;width:52px;height:52px;border-radius:50%;background:rgba(217,119,6,.18);border:1.5px solid rgba(217,119,6,.6);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#fbbf24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div style="max-width:640px;">

            {{-- Eyebrow --}}
            <div class="h-up1 flex items-center gap-3 mb-5">
                <span style="display:inline-block;width:28px;height:2px;background:#d97706;border-radius:2px;flex-shrink:0;"></span>
                <span style="font-size:.6875rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#fbbf24;">{{ $neighborhoodName }} · FalaVizin</span>
            </div>

            {{-- Headline --}}
            <h1 class="h-up2" style="font-family:var(--font-display);font-size:clamp(2.5rem,5.5vw,4rem);font-weight:800;color:#fff;line-height:1.06;letter-spacing:-.03em;margin:0 0 1.25rem;">
                O bairro tem voz.<br>
                <span style="color:#fbbf24;font-style:italic;">Você faz parte disso.</span>
            </h1>

            <p class="h-up3" style="font-size:1.0625rem;color:rgba(255,255,255,.72);line-height:1.72;margin:0 0 2.25rem;max-width:500px;">
                Serviços locais, avisos de vizinhos, eventos e pedidos de ajuda — tudo em um só lugar, feito pela sua comunidade.
            </p>

            {{-- CTAs --}}
            <div class="h-up3 flex flex-col sm:flex-row gap-3 mb-10">
                <a href="{{ route('feed.index') }}"
                   style="display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:.9rem 1.875rem;background:#d97706;color:#fff;font-weight:700;font-size:.9375rem;border-radius:12px;text-decoration:none;letter-spacing:.01em;box-shadow:0 4px 20px rgba(217,119,6,.35);transition:background .15s,transform .12s,box-shadow .15s;"
                   onmouseover="this.style.background='#b45309';this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(180,83,9,.45)'"
                   onmouseout="this.style.background='#d97706';this.style.transform='none';this.style.boxShadow='0 4px 20px rgba(217,119,6,.35)'">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    Ver o Feed
                </a>
                <a href="{{ route('businesses.index') }}"
                   style="display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:.9rem 1.875rem;background:rgba(255,255,255,.07);color:rgba(255,255,255,.85);font-weight:600;font-size:.9375rem;border-radius:12px;text-decoration:none;border:1.5px solid rgba(255,255,255,.14);transition:background .15s,border-color .15s;"
                   onmouseover="this.style.background='rgba(255,255,255,.13)';this.style.borderColor='rgba(255,255,255,.3)'"
                   onmouseout="this.style.background='rgba(255,255,255,.07)';this.style.borderColor='rgba(255,255,255,.14)'">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                    Encontrar Serviços
                </a>
            </div>

            {{-- Live stats --}}
            <div class="h-up4 flex items-center gap-5 flex-wrap" style="padding-top:1.5rem;border-top:1px solid rgba(255,255,255,.08);">
                <div>
                    <div style="font-family:var(--font-display);font-size:1.625rem;font-weight:800;color:#fbbf24;line-height:1;">{{ $heroStats['posts'] }}</div>
                    <div style="font-size:.6875rem;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.08em;margin-top:.2rem;">publicações</div>
                </div>
                <div style="width:1px;height:38px;background:rgba(255,255,255,.1);"></div>
                <div>
                    <div style="font-family:var(--font-display);font-size:1.625rem;font-weight:800;color:#fbbf24;line-height:1;">{{ $heroStats['businesses'] }}</div>
                    <div style="font-size:.6875rem;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.08em;margin-top:.2rem;">negócios locais</div>
                </div>
                <div style="width:1px;height:38px;background:rgba(255,255,255,.1);"></div>
                <div>
                    <div style="font-family:var(--font-display);font-size:1.625rem;font-weight:800;color:#fbbf24;line-height:1;">{{ $heroStats['users'] }}</div>
                    <div style="font-size:.6875rem;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.08em;margin-top:.2rem;">vizinhos</div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════
     CATEGORIAS — responsive pill strip
════════════════════════════════════════════ --}}
<div x-data="{ open: false }" style="background:#fff;border-bottom:1px solid #e7e5e4;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center gap-2">

            {{-- Pills container: single row clipped when closed, wraps when open --}}
            <div class="flex-1 min-w-0" style="padding-top:4px;margin-top:-4px;" :class="open ? '' : 'overflow-hidden'">
                <div class="flex gap-2" :class="open ? 'flex-wrap' : 'flex-nowrap'">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category) }}"
                           class="cat-pill flex-shrink-0 flex items-center gap-2 px-4 py-2 bg-stone-50 border border-stone-200 rounded-full text-sm font-medium text-stone-700 hover:text-amber-700 hover:bg-amber-50 hover:border-amber-300 whitespace-nowrap transition-colors">
                            <x-dynamic-component :component="'heroicon-o-' . $category->icon" class="w-4 h-4 text-amber-600" />
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Ver mais — always visible --}}
            <button
                @click="open = !open"
                class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 bg-amber-50 border border-amber-200 rounded-full text-sm font-semibold text-amber-700 hover:bg-amber-100 hover:border-amber-300 transition-all whitespace-nowrap"
            >
                <span x-text="open ? 'Menos' : 'Ver mais'"></span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </button>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     MAIN CONTENT — 2/3 + 1/3
════════════════════════════════════════════ --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-20">
    <div class="grid lg:grid-cols-3 gap-10">

        {{-- ── LEFT COLUMN (feed + events + requests) ── --}}
        <div class="lg:col-span-2 space-y-12">

            {{-- Últimas do Bairro --}}
            <div data-reveal>
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <p class="sec-eyebrow">Comunidade</p>
                        <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:#1c1917;margin:0;letter-spacing:-.02em;">
                            Últimas do Bairro
                        </h2>
                    </div>
                    <a href="{{ route('feed.index') }}"
                       class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 hover:text-amber-700 transition-colors shrink-0 mb-1">
                        Ver tudo
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>

                @if($recentPosts->isEmpty())
                    <div class="bg-white rounded-2xl border border-stone-200 p-10 text-center">
                        <x-heroicon-o-newspaper class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                        <p class="text-stone-400 text-sm">Nenhum post ainda. Seja o primeiro a publicar!</p>
                        @auth
                            <a href="{{ route('feed.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Publicar agora
                            </a>
                        @endauth
                    </div>
                @else
                    {{-- Feature: first post large --}}
                    @php $featuredPost = $recentPosts->first(); $otherPosts = $recentPosts->skip(1); @endphp

                    <a href="{{ route('feed.show', $featuredPost) }}"
                       class="lift group block bg-white rounded-2xl border border-stone-200 overflow-hidden mb-4">
                        @if($featuredPost->image)
                            <div class="h-52 overflow-hidden">
                                <img src="{{ Storage::url($featuredPost->image) }}"
                                     alt="{{ $featuredPost->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            </div>
                        @else
                            <div class="h-24 overflow-hidden" style="background:linear-gradient(135deg,#fef3c7,#fde68a 60%,#fbbf24);">
                                <div class="h-full flex items-center justify-center">
                                    <x-dynamic-component :component="'heroicon-o-' . ($featuredPost->category->icon ?? 'newspaper')" class="w-10 h-10 text-amber-400 opacity-60"/>
                                </div>
                            </div>
                        @endif
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <x-avatar :user="$featuredPost->user" class="w-7 h-7 text-xs" />
                                <span class="text-sm font-medium text-stone-700">{{ $featuredPost->user->name }}</span>
                                <span class="text-stone-300">·</span>
                                <span class="text-xs text-stone-400">{{ $featuredPost->created_at->diffForHumans() }}</span>
                                <x-category-badge :category="$featuredPost->category" />
                            </div>
                            <h3 style="font-family:var(--font-display);font-size:1.1875rem;font-weight:700;color:#1c1917;margin:0 0 .5rem;line-height:1.3;"
                                class="group-hover:text-amber-700 transition-colors">
                                {{ $featuredPost->title }}
                            </h3>
                            <p class="text-sm text-stone-500 line-clamp-2 mb-4">{{ $featuredPost->body }}</p>
                            <div class="flex items-center gap-4 text-xs text-stone-400">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-chat-bubble-left class="w-3.5 h-3.5"/>
                                    {{ $featuredPost->comments_count ?? 0 }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-hand-thumb-up class="w-3.5 h-3.5"/>
                                    {{ $featuredPost->votes_count ?? 0 }}
                                </span>
                                @if($featuredPost->location)
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-o-map-pin class="w-3.5 h-3.5"/>
                                        {{ $featuredPost->location }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>

                    {{-- Remaining posts --}}
                    @if($otherPosts->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($otherPosts as $post)
                                <x-post-card :post="$post" />
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Eventos Próximos --}}
            @if($upcomingEvents->isNotEmpty())
                <div data-reveal data-reveal-d2>
                    <div class="flex items-end justify-between mb-6">
                        <div>
                            <p class="sec-eyebrow">Agenda</p>
                            <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:#1c1917;margin:0;letter-spacing:-.02em;">
                                Eventos Próximos
                            </h2>
                        </div>
                        <a href="{{ route('categories.show', 'evento') }}"
                           class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 hover:text-amber-700 transition-colors shrink-0 mb-1">
                            Ver todos
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="grid sm:grid-cols-{{ min($upcomingEvents->count(), 3) === 1 ? '1' : (min($upcomingEvents->count(), 3) === 2 ? '2' : '3') }} gap-3">
                        @foreach($upcomingEvents as $event)
                            <a href="{{ route('feed.show', $event) }}"
                               class="lift group flex gap-4 bg-white rounded-2xl border border-stone-200 p-4 hover:border-amber-200">
                                <div class="shrink-0 flex flex-col items-center justify-center w-14 h-14 rounded-xl text-center"
                                     style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #fcd34d;">
                                    <span class="text-xs font-bold text-amber-700 uppercase leading-none">
                                        {{ $event->event_starts_at->isoFormat('MMM') }}
                                    </span>
                                    <span class="text-2xl font-extrabold text-amber-800 leading-tight" style="font-family:var(--font-display);">
                                        {{ $event->event_starts_at->format('d') }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-stone-900 group-hover:text-amber-700 transition-colors leading-snug line-clamp-2 text-sm mb-1">
                                        {{ $event->title }}
                                    </p>
                                    <p class="text-xs text-stone-400">
                                        {{ $event->event_starts_at->isoFormat('HH:mm') }}
                                        @if($event->location)· {{ $event->location }}@endif
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pedidos ao Bairro --}}
            @if($recentRequests->isNotEmpty())
                <div data-reveal data-reveal-d3>
                    <div class="flex items-end justify-between mb-6">
                        <div>
                            <p class="sec-eyebrow">Ajuda mútua</p>
                            <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:#1c1917;margin:0;letter-spacing:-.02em;">
                                Pedidos ao Bairro
                            </h2>
                        </div>
                        <a href="{{ route('categories.show', 'pedido') }}"
                           class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 hover:text-amber-700 transition-colors shrink-0 mb-1">
                            Ver todos
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-3">
                        @foreach($recentRequests as $post)
                            <a href="{{ route('feed.show', $post) }}"
                               class="lift group flex flex-col gap-3 rounded-2xl border p-4"
                               style="background:linear-gradient(135deg,#eff6ff,#dbeafe 80%);border-color:#bfdbfe;">
                                <div class="flex items-center gap-2">
                                    <x-avatar :user="$post->user" class="w-7 h-7 text-xs" />
                                    <span class="text-xs text-stone-500 truncate flex-1">{{ $post->user->name }}</span>
                                    <span class="text-xs text-stone-400 shrink-0">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="font-semibold text-stone-900 group-hover:text-blue-700 transition-colors leading-snug text-sm line-clamp-2 flex-1">
                                    {{ $post->title }}
                                </p>
                                <div class="flex items-center gap-3 text-xs text-stone-400">
                                    <span class="inline-flex items-center gap-1">
                                        <x-heroicon-o-chat-bubble-left class="w-3.5 h-3.5"/>
                                        {{ $post->comments_count }}
                                    </span>
                                    @if($post->location)
                                        <span class="inline-flex items-center gap-1 truncate">
                                            <x-heroicon-o-map-pin class="w-3.5 h-3.5 shrink-0"/>
                                            {{ $post->location }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Conteúdo Patrocinado --}}
            @if($sponsoredPosts->isNotEmpty())
                <div data-reveal>
                    <div class="flex items-center gap-2 mb-5">
                        <x-heroicon-s-bolt class="w-4 h-4 text-amber-500"/>
                        <p class="sec-eyebrow" style="margin:0;">Conteúdo Patrocinado</p>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach($sponsoredPosts as $post)
                            <a href="{{ route('feed.show', $post) }}"
                               class="lift group relative flex flex-col bg-white rounded-2xl border border-amber-200 overflow-hidden">
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200">
                                        <x-heroicon-s-bolt class="w-3 h-3"/>Patrocinado
                                    </span>
                                </div>
                                @if($post->image)
                                    <div class="h-32 overflow-hidden">
                                        <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-400"/>
                                    </div>
                                @else
                                    <div class="h-20 flex items-center justify-center" style="background:linear-gradient(135deg,#fef3c7,#fde68a);">
                                        <x-dynamic-component :component="'heroicon-o-' . ($post->category->icon ?? 'newspaper')" class="w-9 h-9 text-amber-400"/>
                                    </div>
                                @endif
                                <div class="p-4 flex flex-col flex-1">
                                    <x-category-badge :category="$post->category" class="mb-2"/>
                                    <h3 class="font-semibold text-stone-900 text-sm leading-snug mb-1 group-hover:text-amber-700 transition-colors line-clamp-2">{{ $post->title }}</h3>
                                    <p class="text-xs text-stone-400 line-clamp-2 flex-1">{{ $post->body }}</p>
                                    <div class="mt-3 flex items-center justify-between text-xs text-stone-400">
                                        <span>{{ $post->user->name }}</span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-heroicon-o-hand-thumb-up class="w-3.5 h-3.5"/>{{ $post->votes_count }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>{{-- /left column --}}

        {{-- ── RIGHT SIDEBAR ── --}}
        <div class="space-y-8">

            {{-- Pulso do Bairro --}}
            <div data-reveal class="rounded-2xl overflow-hidden" style="background:linear-gradient(135deg,#1c1917,#27180a);border:1px solid rgba(217,119,6,.2);">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="pulse-dot w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                            <span style="font-size:.6875rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(251,191,36,.75);">Pulso desta semana</span>
                        </div>
                        <a href="{{ route('pulso.index') }}"
                           style="font-size:.75rem;color:rgba(251,191,36,.65);text-decoration:none;font-weight:500;transition:color .15s;"
                           onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='rgba(251,191,36,.65)'">
                            Ver tudo →
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl p-4 text-center" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);">
                            <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:#fbbf24;line-height:1;">{{ $pulsoPostsThisWeek }}</div>
                            <div style="font-size:.6875rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-top:.25rem;">posts</div>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);">
                            <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:#34d399;line-height:1;">{{ $pulsoResolvedThisWeek }}</div>
                            <div style="font-size:.6875rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-top:.25rem;">resolvidos</div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('pulso.index') }}"
                   style="display:flex;align-items:center;justify-content:center;gap:6px;padding:.75rem;background:rgba(217,119,6,.12);border-top:1px solid rgba(217,119,6,.15);color:rgba(251,191,36,.8);font-size:.8125rem;font-weight:600;text-decoration:none;transition:background .15s;"
                   onmouseover="this.style.background='rgba(217,119,6,.22)'" onmouseout="this.style.background='rgba(217,119,6,.12)'">
                    Ver análise completa do bairro
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Em Destaque --}}
            <div data-reveal data-reveal-d2>
                <div class="flex items-end justify-between mb-4">
                    <div>
                        <p class="sec-eyebrow">Parceiros</p>
                        <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:800;color:#1c1917;margin:0;letter-spacing:-.02em;">Em Destaque</h2>
                    </div>
                    <a href="{{ route('businesses.index') }}"
                       class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors mb-1">
                        Ver tudo →
                    </a>
                </div>

                @if($featuredBusinesses->isEmpty())
                    <div class="bg-amber-50 rounded-2xl border border-amber-100 p-6 text-center">
                        <x-heroicon-s-star class="w-6 h-6 text-amber-300 mx-auto mb-2"/>
                        <p class="text-sm text-stone-400">Nenhum negócio em destaque ainda.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden divide-y divide-stone-100">
                        @foreach($featuredBusinesses as $business)
                            <a href="{{ route('businesses.show', $business) }}"
                               class="biz-row flex items-center gap-3 p-3.5 group">
                                <div class="w-11 h-11 rounded-xl overflow-hidden bg-amber-50 shrink-0 border border-amber-100">
                                    @if($business->coverPhoto)
                                        <img src="{{ Storage::url($business->coverPhoto->path) }}" alt="{{ $business->name }}" class="w-full h-full object-cover"/>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <x-dynamic-component :component="'heroicon-o-' . ($business->category->icon ?? 'building-storefront')" class="w-5 h-5 text-amber-400"/>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-stone-900 truncate group-hover:text-amber-700 transition-colors">{{ $business->name }}</p>
                                    <p class="text-xs text-stone-400 truncate">{{ $business->neighborhood }}</p>
                                </div>
                                <x-heroicon-s-star class="w-4 h-4 text-amber-400 shrink-0"/>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Calendário de Eventos --}}
            <div data-reveal data-reveal-d2>
                <livewire:events.event-calendar />
            </div>

            {{-- Promoções --}}
            @if($recentPromotions->isNotEmpty())
                <div data-reveal data-reveal-d3>
                    <div class="flex items-end justify-between mb-4">
                        <div>
                            <p class="sec-eyebrow">Ofertas</p>
                            <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:800;color:#1c1917;margin:0;letter-spacing:-.02em;">Promoções</h2>
                        </div>
                        <a href="{{ route('promotions.index') }}"
                           class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors mb-1">
                            Ver todas →
                        </a>
                    </div>
                    <div class="space-y-2">
                        @foreach($recentPromotions as $promotion)
                            <a href="{{ route('businesses.show', $promotion->business) }}"
                               class="promo-card group block rounded-xl border border-amber-100 p-3.5"
                               style="background:linear-gradient(to right,#fffbeb,#fff);">
                                <p class="text-xs font-semibold text-amber-600 truncate mb-0.5">{{ $promotion->business->name }}</p>
                                <p class="text-sm font-medium text-stone-800 group-hover:text-amber-700 transition-colors leading-snug line-clamp-2">
                                    {{ $promotion->title }}
                                </p>
                                @if($promotion->ends_at)
                                    <p class="text-xs text-stone-400 mt-1.5">Até {{ $promotion->ends_at->format('d/m') }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- CTA cadastro (visitantes) --}}
            @guest
                <div data-reveal class="rounded-2xl p-5 text-center" style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #fcd34d;">
                    <x-heroicon-o-user-plus class="w-8 h-8 text-amber-600 mx-auto mb-3"/>
                    <p style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:#1c1917;margin:0 0 .4rem;">Faça parte da comunidade</p>
                    <p class="text-sm text-stone-600 mb-4">Publique, comente, vote e ajude seus vizinhos.</p>
                    <a href="{{ route('register') }}"
                       style="display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:.7rem 1.5rem;background:#d97706;color:#fff;font-weight:700;font-size:.875rem;border-radius:10px;text-decoration:none;transition:background .15s;"
                       onmouseover="this.style.background='#b45309'" onmouseout="this.style.background='#d97706'">
                        Criar conta grátis →
                    </a>
                </div>
            @endguest

        </div>{{-- /sidebar --}}

    </div>
</div>

@push('scripts')
<script>
    // Scroll-triggered reveals
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('on'), i * 60);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));
</script>
@endpush

</x-app-layout>

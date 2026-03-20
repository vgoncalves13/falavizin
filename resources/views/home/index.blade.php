<x-app-layout>
    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-amber-50 to-stone-100 border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <div class="max-w-2xl">
                <h1 class="text-4xl lg:text-5xl font-bold text-stone-900 leading-tight mb-4" style="font-family: var(--font-display)">
                    O que está acontecendo<br>
                    <span class="text-amber-600">no seu bairro?</span>
                </h1>
                <p class="text-lg text-stone-600 mb-8">
                    Serviços, eventos, avisos e muito mais — tudo do seu bairro, em um só lugar.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('feed.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-xl transition-colors duration-150">
                        <x-heroicon-o-newspaper class="w-5 h-5" />
                        Ver o Feed
                    </a>
                    <a href="{{ route('businesses.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-stone-50 text-stone-700 font-medium rounded-xl border border-stone-200 transition-colors duration-150">
                        <x-heroicon-o-building-storefront class="w-5 h-5" />
                        Encontrar Serviços
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-xl font-bold text-stone-900 mb-6" style="font-family: var(--font-display)">Categorias</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                   class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150">
                    <x-dynamic-component :component="'heroicon-o-' . $category->icon" class="w-6 h-6 text-amber-600" />
                    <span class="text-xs font-medium text-stone-700 text-center">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Promoções ativas -->
    @if($recentPromotions->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">Promoções</h2>
                <a href="{{ route('promotions.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700">
                    Ver todas →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($recentPromotions as $promotion)
                    <a href="{{ route('businesses.show', $promotion->business) }}"
                       class="group block bg-gradient-to-br from-amber-50 to-white rounded-xl border border-amber-200 p-4 hover:shadow-md transition-shadow duration-200">
                        <p class="text-xs font-medium text-amber-700 mb-1 truncate">{{ $promotion->business->name }}</p>
                        <p class="font-semibold text-stone-900 text-sm leading-snug group-hover:text-amber-700 transition-colors">
                            {{ $promotion->title }}
                        </p>
                        @if($promotion->ends_at)
                            <p class="text-xs text-stone-400 mt-2">Válido até {{ $promotion->ends_at->format('d/m') }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Eventos Próximos -->
    @if($upcomingEvents->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">Eventos Próximos</h2>
                <a href="{{ route('categories.show', 'evento') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700">
                    Ver todos →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($upcomingEvents as $event)
                    <a href="{{ route('feed.show', $event) }}"
                       class="group flex gap-4 bg-white rounded-xl border border-stone-200 p-4 hover:shadow-md hover:border-amber-200 transition-all duration-200">
                        {{-- Data --}}
                        <div class="shrink-0 flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-center">
                            <span class="text-xs font-bold text-amber-700 uppercase leading-none">
                                {{ $event->event_starts_at->isoFormat('MMM') }}
                            </span>
                            <span class="text-xl font-bold text-amber-800 leading-none">
                                {{ $event->event_starts_at->format('d') }}
                            </span>
                        </div>
                        {{-- Info --}}
                        <div class="min-w-0">
                            <p class="font-semibold text-stone-900 group-hover:text-amber-700 transition-colors leading-snug line-clamp-2 text-sm">
                                {{ $event->title }}
                            </p>
                            <p class="text-xs text-stone-400 mt-1">
                                {{ $event->event_starts_at->isoFormat('HH:mm') }}
                                @if($event->location)
                                    · {{ $event->location }}
                                @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Posts Patrocinados -->
    @if($sponsoredPosts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <div class="flex items-center gap-2 mb-4">
                <x-heroicon-s-bolt class="w-4 h-4 text-amber-500" />
                <h2 class="text-base font-semibold text-stone-600 uppercase tracking-wide">Conteúdo Patrocinado</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($sponsoredPosts as $post)
                    <a href="{{ route('feed.show', $post) }}"
                       class="group relative flex flex-col bg-white rounded-xl border border-amber-300 overflow-hidden hover:shadow-md transition-shadow duration-200">

                        {{-- Badge --}}
                        <div class="absolute top-3 right-3 z-10">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200">
                                <x-heroicon-s-bolt class="w-3 h-3" />
                                Patrocinado
                            </span>
                        </div>

                        {{-- Imagem ou fundo âmbar --}}
                        @if($post->image)
                            <div class="h-36 overflow-hidden">
                                <img src="{{ Storage::url($post->image) }}"
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            </div>
                        @else
                            <div class="h-24 bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center">
                                <x-dynamic-component
                                    :component="'heroicon-o-' . ($post->category->icon ?? 'newspaper')"
                                    class="w-10 h-10 text-amber-300" />
                            </div>
                        @endif

                        {{-- Conteúdo --}}
                        <div class="p-4 flex flex-col flex-1">
                            <div class="mb-2">
                                <x-category-badge :category="$post->category" />
                            </div>
                            <h3 class="font-semibold text-stone-900 leading-snug mb-1 group-hover:text-amber-700 transition-colors line-clamp-2">
                                {{ $post->title }}
                            </h3>
                            <p class="text-xs text-stone-500 line-clamp-2 flex-1">{{ $post->body }}</p>
                            <div class="mt-3 flex items-center justify-between text-xs text-stone-400">
                                <span>{{ $post->user->name }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-hand-thumb-up class="w-3.5 h-3.5" />
                                    {{ $post->votes_count }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Feed Recente + Negócios em Destaque -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16">
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Feed Recente -->
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">Últimas do Bairro</h2>
                    <a href="{{ route('feed.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700">
                        Ver tudo →
                    </a>
                </div>

                @if($recentPosts->isEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                        <x-heroicon-o-newspaper class="w-8 h-8 text-stone-300 mx-auto mb-2" />
                        <p class="text-sm text-stone-500">Nenhum post ainda. Seja o primeiro!</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentPosts as $post)
                            <x-post-card :post="$post" />
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Sidebar: Em Destaque -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">Em Destaque</h2>
                    <a href="{{ route('businesses.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700">
                        Ver tudo →
                    </a>
                </div>

                @if($featuredBusinesses->isEmpty())
                    <div class="bg-amber-50 rounded-xl border border-amber-200 p-6 text-center">
                        <x-heroicon-s-star class="w-6 h-6 text-amber-400 mx-auto mb-2" />
                        <p class="text-sm text-stone-500">Nenhum negócio em destaque.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($featuredBusinesses as $business)
                            <a href="{{ route('businesses.show', $business) }}"
                               class="flex items-center gap-3 p-3 bg-white rounded-xl border border-amber-200 hover:shadow-sm transition-shadow duration-150 group">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-stone-100 shrink-0">
                                    @if($business->coverPhoto)
                                        <img src="{{ Storage::url($business->coverPhoto->path) }}" alt="{{ $business->name }}" class="w-full h-full object-cover" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <x-dynamic-component :component="'heroicon-o-' . ($business->category->icon ?? 'building-storefront')" class="w-6 h-6 text-stone-300" />
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-stone-900 truncate group-hover:text-amber-700 transition-colors">
                                        {{ $business->name }}
                                    </p>
                                    <p class="text-xs text-stone-400 truncate">{{ $business->neighborhood }}</p>
                                </div>
                                <x-heroicon-s-star class="w-4 h-4 text-amber-400 shrink-0 ml-auto" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-app-layout>

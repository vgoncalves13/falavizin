<x-app-layout>
    <x-slot name="title">{{ $query->isNotEmpty() ? 'Busca: ' . $query : ($neighborhood ? 'Buscando em ' . $neighborhood->name : 'Buscar') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header com campo de busca --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-stone-900 mb-4" style="font-family: var(--font-display)">
                @if($neighborhood)
                    Buscando em {{ $neighborhood->name }}
                @else
                    Buscar no FalaVizin
                @endif
            </h1>
            <form action="{{ $neighborhood ? route('neighborhood.search.index', $neighborhood->routeParameters()) : route('search.index') }}" method="GET">
                <div class="relative max-w-xl">
                    <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                    <input
                        type="search"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Buscar posts, serviços, bairros..."
                        autofocus
                        class="w-full pl-11 pr-4 py-3 bg-white border border-stone-200 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors duration-150 text-sm"
                    />
                    <button
                        type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150"
                    >
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        @if ($query->isEmpty())
            {{-- Estado vazio: nenhuma query --}}
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-full bg-stone-100 flex items-center justify-center mb-4">
                    <x-heroicon-o-magnifying-glass class="w-8 h-8 text-stone-400" />
                </div>
                <p class="text-stone-600 font-medium mb-1">O que você está procurando?</p>
                <p class="text-sm text-stone-400">Digite um termo acima para encontrar posts, serviços e comércios do bairro.</p>
            </div>
        @elseif ($posts->isEmpty() && $businesses->isEmpty())
            {{-- Sem resultados --}}
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-full bg-stone-100 flex items-center justify-center mb-4">
                    <x-heroicon-o-face-frown class="w-8 h-8 text-stone-400" />
                </div>
                <p class="text-stone-600 font-medium mb-1">Nenhum resultado para "{{ $query }}"</p>
                <p class="text-sm text-stone-400">Tente outros termos ou verifique a ortografia.</p>
            </div>
        @else
            {{-- Total de resultados --}}
            @php $total = $posts->count() + $businesses->count(); @endphp
            <p class="text-sm text-stone-500 mb-6">
                <span class="font-semibold text-stone-700">{{ $total }}</span>
                {{ $total === 1 ? 'resultado' : 'resultados' }} para
                "<span class="font-semibold text-stone-700">{{ $query }}</span>"
            </p>

            <div class="space-y-10">
                {{-- Seção: Posts do feed --}}
                @if ($posts->isNotEmpty())
                    <section>
                        <div class="flex items-center gap-2 mb-4">
                            <x-heroicon-o-newspaper class="w-5 h-5 text-amber-600" />
                            <h2 class="text-lg font-semibold text-stone-900" style="font-family: var(--font-display)">
                                Posts do Feed
                            </h2>
                            <span class="text-sm text-stone-400">({{ $posts->count() }})</span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($posts as $post)
                                <a
                                    href="{{ route('feed.show', $post) }}"
                                    class="block bg-white rounded-xl border border-stone-200 p-4 hover:shadow-md hover:border-stone-300 transition-all duration-200"
                                >
                                    <div class="flex items-start gap-3">
                                        <x-avatar :user="$post->user" class="w-8 h-8 text-xs mt-0.5" />
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                                <span class="text-xs text-stone-500">{{ $post->user->name }}</span>
                                                <span class="text-stone-300 text-xs">·</span>
                                                <span class="text-xs text-stone-400">{{ $post->created_at->diffForHumans() }}</span>
                                                <x-category-badge :category="$post->category" />
                                            </div>
                                            <h3 class="font-semibold text-stone-900 leading-snug hover:text-amber-700 transition-colors duration-150">
                                                {{ $post->title }}
                                            </h3>
                                            <p class="mt-1 text-sm text-stone-500 line-clamp-2">{{ $post->body }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Seção: Serviços e Comércios --}}
                @if ($businesses->isNotEmpty())
                    <section>
                        <div class="flex items-center gap-2 mb-4">
                            <x-heroicon-o-building-storefront class="w-5 h-5 text-amber-600" />
                            <h2 class="text-lg font-semibold text-stone-900" style="font-family: var(--font-display)">
                                Serviços e Comércios
                            </h2>
                            <span class="text-sm text-stone-400">({{ $businesses->count() }})</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($businesses as $business)
                                <a
                                    href="{{ route('businesses.show', $business) }}"
                                    class="block bg-white rounded-xl border border-stone-200 p-4 hover:shadow-md hover:border-stone-300 transition-all duration-200 {{ $business->plan->value === 'featured' ? 'border-amber-300 ring-1 ring-amber-200' : '' }}"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="shrink-0 w-10 h-10 rounded-lg bg-stone-100 flex items-center justify-center">
                                            <x-dynamic-component
                                                :component="'heroicon-o-' . ($business->category->icon ?? 'building-storefront')"
                                                class="w-5 h-5 text-stone-400"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <h3 class="font-semibold text-stone-900 leading-snug truncate hover:text-amber-700 transition-colors duration-150">
                                                    {{ $business->name }}
                                                </h3>
                                                @if ($business->plan->value === 'featured')
                                                    <x-heroicon-s-star class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                                @endif
                                            </div>
                                            <x-category-badge :category="$business->category" />
                                            <div class="mt-1.5 flex items-center gap-1 text-xs text-stone-400">
                                                <x-heroicon-o-map-pin class="w-3 h-3 shrink-0" />
                                                <span class="truncate">{{ $business->neighborhood }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>

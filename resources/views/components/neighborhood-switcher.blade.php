@props([
    'current' => null,
    'neighborhoods' => collect(),
    'mobile' => false,
])

@if($current && $neighborhoods->isNotEmpty())
    <div
        {{ $attributes }}
        x-data="{
            open: false,
            query: '',
            names: @js($neighborhoods->pluck('name')->values()->all()),
            normalize(value) {
                return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
            },
            matches(name) {
                return this.normalize(name).includes(this.normalize(this.query))
            },
        }"
        x-on:keydown.escape.window="open = false; query = ''"
        x-on:click.outside="open = false; query = ''"
        class="{{ $mobile ? 'px-4 py-3' : 'relative' }}"
    >
        <button
            x-on:click="open = !open; if (open) $nextTick(() => $refs.search.focus())"
            type="button"
            class="flex items-center gap-2 w-full {{ $mobile ? 'px-3 py-2 rounded-lg hover:bg-stone-100' : 'px-3 py-1.5 rounded-lg hover:bg-stone-100 border border-stone-200' }} transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
            aria-haspopup="true"
            x-bind:aria-expanded="open.toString()"
        >
            <x-heroicon-o-map-pin class="w-4 h-4 text-amber-600 shrink-0" />
            <span class="text-sm font-medium text-stone-700 truncate">{{ $current->name }}</span>
            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-stone-400 shrink-0 transition-transform duration-150" x-bind:class="open ? 'rotate-180' : ''" />
        </button>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="{{ $mobile ? 'mt-1' : 'absolute left-0 mt-1 w-64 z-50' }} bg-white rounded-xl border border-stone-200 shadow-lg overflow-hidden"
            style="display: none;"
        >
            <div class="border-b border-stone-100 p-2">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400 pointer-events-none" />
                    <input
                        data-neighborhood-search
                        x-ref="search"
                        x-model.debounce.150ms="query"
                        type="search"
                        placeholder="Buscar bairro..."
                        aria-label="Buscar bairro"
                        autocomplete="off"
                        class="w-full rounded-lg border border-stone-200 bg-stone-50 py-2 pl-8 pr-3 text-sm text-stone-800 placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                    />
                </div>
            </div>

            @php
                $listRoutes = ['neighborhood.feed.index', 'neighborhood.businesses.index', 'neighborhood.promotions.index', 'neighborhood.pulso.index', 'neighborhood.events.index', 'neighborhood.search.index'];
                $currentRouteName = request()->route()?->getName();
                $isListRoute = in_array($currentRouteName, $listRoutes, true);
            @endphp

            <div data-neighborhood-list class="max-h-72 overflow-y-auto overscroll-contain py-1">
                @foreach($neighborhoods as $neighborhood)
                    @php
                        $isActive = $neighborhood->id === $current->id;

                        if ($isListRoute) {
                            $params = $neighborhood->routeParameters();
                            foreach (['q', 'categoryId', 'sortBy'] as $qp) {
                                if (request()->has($qp)) {
                                    $params[$qp] = request($qp);
                                }
                            }
                            $url = route($currentRouteName, $params);
                        } else {
                            $url = route('neighborhood.home', $neighborhood->routeParameters());
                        }
                    @endphp

                    <a
                        x-show="matches(@js($neighborhood->name))"
                        href="{{ $url }}"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm transition-colors duration-150 {{ $isActive ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-stone-700 hover:bg-stone-50' }}"
                        @if($isActive) aria-current="true" @endif
                    >
                        <x-heroicon-o-map-pin class="w-3.5 h-3.5 {{ $isActive ? 'text-amber-600' : 'text-stone-400' }} shrink-0" />
                        <span class="truncate">{{ $neighborhood->name }}</span>
                        @if($isActive)
                            <x-heroicon-o-check class="w-4 h-4 text-amber-600 ml-auto shrink-0" />
                        @endif
                    </a>
                @endforeach

                <p x-show="query && !names.some(name => matches(name))" class="px-4 py-6 text-center text-sm text-stone-500" style="display: none;">
                    Nenhum bairro encontrado
                </p>
            </div>

            @auth
                @if($current->id !== auth()->user()->neighborhood_id)
                    <form method="POST" action="{{ route('neighborhoods.update') }}" class="border-t border-stone-100">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="neighborhood_id" value="{{ $current->id }}">
                        <button
                            type="submit"
                            class="flex items-center gap-2 w-full px-4 py-2 text-sm text-amber-700 hover:bg-amber-50 transition-colors duration-150"
                        >
                            <x-heroicon-o-star class="w-3.5 h-3.5" />
                            Tornar meu bairro principal
                        </button>
                    </form>
                @endif
            @endauth
        </div>
    </div>
@endif

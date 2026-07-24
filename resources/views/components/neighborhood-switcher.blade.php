@props([
    'current' => null,
    'neighborhoods' => collect(),
    'mobile' => false,
])

@if($current && $neighborhoods->isNotEmpty())
    <div
        {{ $attributes }}
        x-data="{ open: false }"
        x-on:keydown.escape.window="open = false"
        x-on:click.outside="open = false"
        class="{{ $mobile ? 'px-4 py-3' : 'relative' }}"
    >
        <button
            x-on:click="open = !open"
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
            <div class="py-1">
                @php
                    $listRoutes = ['neighborhood.feed.index', 'neighborhood.businesses.index', 'neighborhood.promotions.index', 'neighborhood.pulso.index', 'neighborhood.events.index', 'neighborhood.search.index'];
                    $currentRouteName = request()->route()?->getName();
                    $isListRoute = in_array($currentRouteName, $listRoutes, true);
                @endphp

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

                @auth
                    @if($current->id !== auth()->user()->neighborhood_id)
                        <div class="border-t border-stone-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('neighborhoods.update') }}">
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
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>
@endif

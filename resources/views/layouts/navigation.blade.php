@php
    $displayNeighborhood = $currentNeighborhood ?? auth()->user()?->primaryNeighborhood ?? null;
@endphp
<nav x-data="{ open: false, searchOpen: false }" class="bg-white border-b border-stone-200 sticky top-0 z-50" aria-label="Navegação principal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center gap-4 lg:gap-6">
                <a href="{{ route('home') }}" class="shrink-0 flex items-center">
                    <img src="{{ asset('assets/images/logotipo.png') }}" alt="{{ config('app.name') }}" class="hidden sm:block h-9 w-auto">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}" class="block sm:hidden h-9 w-9 rounded-xl">
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-1">
                    @if($displayNeighborhood)
                        <a href="{{ route('neighborhood.feed.index', $displayNeighborhood->routeParameters()) }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('neighborhood.feed.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-600 hover:text-stone-900 hover:bg-stone-50' }}">
                            <x-heroicon-o-newspaper class="w-4 h-4" />
                            <span class="hidden lg:inline">Feed</span>
                        </a>
                        <a href="{{ route('neighborhood.businesses.index', $displayNeighborhood->routeParameters()) }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('neighborhood.businesses.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-600 hover:text-stone-900 hover:bg-stone-50' }}">
                            <x-heroicon-o-building-storefront class="w-4 h-4" />
                            <span class="hidden lg:inline">Serviços</span>
                        </a>
                        <a href="{{ route('neighborhood.promotions.index', $displayNeighborhood->routeParameters()) }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('neighborhood.promotions.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-600 hover:text-stone-900 hover:bg-stone-50' }}">
                            <x-heroicon-o-tag class="w-4 h-4" />
                            <span class="hidden lg:inline">Promoções</span>
                        </a>
                    @endif

                    <!-- Secondary links: icons only on md, full on lg+ -->
                    <a href="{{ route('ranking.index') }}"
                       class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('ranking.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-50' }}">
                        <x-heroicon-o-trophy class="w-4 h-4" />
                        <span class="hidden lg:inline">Ranking</span>
                    </a>
                    @if($displayNeighborhood)
                        <a href="{{ route('neighborhood.pulso.index', $displayNeighborhood->routeParameters()) }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('neighborhood.pulso.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-50' }}">
                            <x-heroicon-o-signal class="w-4 h-4" />
                            <span class="hidden lg:inline">Pulso</span>
                        </a>
                        <a href="{{ route('neighborhood.events.index', $displayNeighborhood->routeParameters()) }}"
                           class="hidden lg:flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('neighborhood.events.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-50' }}">
                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                            Eventos
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right side: search + neighborhood + actions -->
            <div class="flex items-center gap-2">
                <!-- Search (desktop) -->
                <form action="{{ $displayNeighborhood ? route('neighborhood.search.index', $displayNeighborhood->routeParameters()) : route('search.index') }}" method="GET" role="search" class="hidden lg:block">
                    <div class="relative">
                        <label for="search-desktop" class="sr-only">Buscar</label>
                        <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                        <input id="search-desktop" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar..."
                            class="pl-8 pr-3 py-1.5 text-sm bg-stone-100 border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 w-44 xl:w-52 transition-colors duration-150" />
                    </div>
                </form>

                <!-- Search toggle (md only) -->
                <button @click="searchOpen = !searchOpen" class="lg:hidden p-2 rounded-lg text-stone-500 hover:text-stone-700 hover:bg-stone-50 transition-colors">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                </button>

                <!-- Neighborhood switcher (desktop) -->
                @if($displayNeighborhood && isset($navigationNeighborhoods))
                    <div data-neighborhood-switcher-desktop class="hidden xl:block">
                        <x-neighborhood-switcher :current="$displayNeighborhood" :neighborhoods="$navigationNeighborhoods" />
                    </div>
                @endif

                @auth
                    <!-- Notification bell -->
                    <div data-navbar-notification>
                        <livewire:notifications.notification-bell />
                    </div>

                    <!-- Publicar (desktop) -->
                    @if($displayNeighborhood)
                        <a href="{{ route('neighborhood.feed.create', $displayNeighborhood->routeParameters()) }}"
                           class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            <span class="hidden lg:inline">Publicar</span>
                        </a>
                    @endif

                    <!-- User dropdown -->
                    <div class="relative hidden sm:block">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-1.5 p-1.5 rounded-lg text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition-colors duration-150">
                                    <x-avatar :user="Auth::user()" class="w-7 h-7 text-xs" />
                                    <x-heroicon-o-chevron-down class="w-3.5 h-3.5 hidden lg:block" />
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="px-4 py-2.5 border-b border-stone-100">
                                    <p class="text-sm font-medium text-stone-900 truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">{{ Auth::user()->email }}</p>
                                </div>
                                <x-dropdown-link :href="route('profile.account')">Minha conta</x-dropdown-link>
                                <x-dropdown-link :href="route('profile.edit')">Editar perfil</x-dropdown-link>
                                <button type="button" data-pwa-install class="block w-full px-4 py-2 text-start text-sm text-stone-700 hover:bg-stone-50">
                                    Instalar app
                                </button>
                                @if(Auth::user()->is_admin || Auth::user()->isModerator())
                                    @php
                                        $pendingCount = \Illuminate\Support\Facades\Cache::remember('admin:moderation_count', 120, fn () =>
                                            \App\Models\Post::where('status', 'pending')->count() +
                                            \App\Models\Business::where('status', 'pending')->count() +
                                            \App\Models\Promotion::where('status', 'pending')->count() +
                                            \App\Models\Business::whereNotNull('claim_user_id')->count() +
                                            \App\Models\Post::whereNotNull('reported_at')->where('status', 'approved')->count() +
                                            \App\Models\Business::whereNotNull('reported_at')->where('status', 'approved')->count() +
                                            \App\Models\Promotion::whereNotNull('reported_at')->where('status', 'approved')->count()
                                        );
                                    @endphp
                                    <div class="border-t border-stone-100 pt-1">
                                        <x-dropdown-link :href="route('admin.moderation.index')">
                                            <span class="flex items-center justify-between gap-2">
                                                Moderação
                                                @if($pendingCount > 0)
                                                    <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $pendingCount > 99 ? '99+' : $pendingCount }}</span>
                                                @endif
                                            </span>
                                        </x-dropdown-link>
                                        @if(Auth::user()->is_admin)
                                            <x-dropdown-link :href="route('admin.stats')">Estatísticas</x-dropdown-link>
                                            <x-dropdown-link :href="route('admin.google-places-import')">Importar Google</x-dropdown-link>
                                            <x-dropdown-link :href="route('admin.neighborhoods')">Bairros</x-dropdown-link>
                                        @endif
                                    </div>
                                @endif
                                <div class="border-t border-stone-100 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full px-4 py-2 text-start text-sm text-stone-700 hover:bg-stone-50">Sair</button>
                                    </form>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:block text-sm font-medium text-stone-600 hover:text-stone-900 px-3 py-2">Entrar</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">Cadastrar</a>
                @endauth

                <!-- Mobile menu button -->
                <button @click="open = !open" class="inline-flex md:hidden items-center justify-center p-2 rounded-lg text-stone-500 hover:text-stone-700 hover:bg-stone-50 transition"
                    :aria-expanded="open.toString()" aria-controls="mobile-menu" aria-label="Menu">
                    <x-heroicon-o-bars-3 x-show="!open" class="h-5 w-5" />
                    <x-heroicon-o-x-mark x-show="open" class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>

    <!-- Search bar (md screens, expandable) -->
    <div x-show="searchOpen" x-transition class="lg:hidden border-t border-stone-100 px-4 py-2 bg-white">
        <form action="{{ $displayNeighborhood ? route('neighborhood.search.index', $displayNeighborhood->routeParameters()) : route('search.index') }}" method="GET" role="search">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar posts, serviços..." autofocus
                    class="w-full pl-9 pr-3 py-2 text-sm bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" />
            </div>
        </form>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" x-transition id="mobile-menu" class="md:hidden border-t border-stone-200 bg-white">
        @if($displayNeighborhood && isset($navigationNeighborhoods))
            <div data-neighborhood-switcher-mobile class="border-b border-stone-100 px-4 py-3">
                <x-neighborhood-switcher :current="$displayNeighborhood" :neighborhoods="$navigationNeighborhoods" mobile />
            </div>
        @endif

        <div class="px-3 py-2 space-y-0.5">
            @if($displayNeighborhood)
                <a href="{{ route('neighborhood.feed.index', $displayNeighborhood->routeParameters()) }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('neighborhood.feed.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">
                    <x-heroicon-o-newspaper class="w-5 h-5 text-stone-400" /> Feed
                </a>
                <a href="{{ route('neighborhood.businesses.index', $displayNeighborhood->routeParameters()) }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('neighborhood.businesses.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">
                    <x-heroicon-o-building-storefront class="w-5 h-5 text-stone-400" /> Serviços
                </a>
                <a href="{{ route('neighborhood.promotions.index', $displayNeighborhood->routeParameters()) }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('neighborhood.promotions.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">
                    <x-heroicon-o-tag class="w-5 h-5 text-stone-400" /> Promoções
                </a>
                <a href="{{ route('neighborhood.events.index', $displayNeighborhood->routeParameters()) }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('neighborhood.events.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-stone-400" /> Eventos
                </a>
                <a href="{{ route('neighborhood.pulso.index', $displayNeighborhood->routeParameters()) }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('neighborhood.pulso.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">
                    <x-heroicon-o-signal class="w-5 h-5 text-stone-400" /> Pulso
                </a>
            @endif
            <a href="{{ route('ranking.index') }}"
               class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('ranking.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">
                <x-heroicon-o-trophy class="w-5 h-5 text-stone-400" /> Ranking
            </a>
        </div>

        <div class="border-t border-stone-100 px-3 py-3">
            @auth
                @if($displayNeighborhood)
                    <a href="{{ route('neighborhood.feed.create', $displayNeighborhood->routeParameters()) }}"
                       class="flex items-center gap-2 px-3 py-2.5 mb-2 bg-amber-600 text-white rounded-lg text-sm font-medium">
                        <x-heroicon-o-plus class="w-4 h-4" /> Publicar
                    </a>
                @endif
                <a href="{{ route('profile.account') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">Minha conta</a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">Editar perfil</a>
                @if(Auth::user()->is_admin || Auth::user()->isModerator())
                    <a href="{{ route('admin.moderation.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">
                        <x-heroicon-o-shield-check class="w-4 h-4" /> Moderação
                    </a>
                    @if(Auth::user()->is_admin)
                        <a href="{{ route('admin.stats') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">Estatísticas</a>
                        <a href="{{ route('admin.neighborhoods') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">Bairros</a>
                    @endif
                @endif
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">Sair</button>
                </form>
            @else
                <div class="flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2.5 border border-stone-300 text-stone-700 rounded-lg text-sm font-medium hover:bg-stone-50">Entrar</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2.5 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">Cadastrar</a>
                </div>
            @endauth
        </div>
    </div>
</nav>

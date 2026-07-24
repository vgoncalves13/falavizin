<nav x-data="{ open: false }" class="bg-white border-b border-stone-200 sticky top-0 z-50" aria-label="Navegação principal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo + Nav Links -->
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="shrink-0 flex items-center">
                    <img
                        src="{{ asset('assets/images/logotipo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="hidden sm:block h-10 w-auto"
                    >
                    <img
                        src="{{ asset('assets/images/logo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="block sm:hidden h-10 w-10 rounded-xl"
                    >
                </a>

                <div class="hidden sm:flex items-center gap-6">
                    <a href="{{ route('feed.index') }}"
                       class="flex items-center gap-1.5 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('feed.*') ? 'text-amber-600' : 'text-stone-600 hover:text-stone-900' }}">
                        <x-heroicon-o-newspaper class="w-4 h-4" />
                        Feed
                    </a>
                    <a href="{{ route('businesses.index') }}"
                       class="flex items-center gap-1.5 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('businesses.*') ? 'text-amber-600' : 'text-stone-600 hover:text-stone-900' }}">
                        <x-heroicon-o-building-storefront class="w-4 h-4" />
                        Serviços
                    </a>
                    <a href="{{ route('promotions.index') }}"
                       class="flex items-center gap-1.5 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('promotions.*') ? 'text-amber-600' : 'text-stone-600 hover:text-stone-900' }}">
                        <x-heroicon-o-tag class="w-4 h-4" />
                        Promoções
                    </a>
                    <a href="{{ route('ranking.index') }}"
                       class="flex items-center gap-1.5 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('ranking.*') ? 'text-amber-600' : 'text-stone-600 hover:text-stone-900' }}">
                        <x-heroicon-o-trophy class="w-4 h-4" />
                        Ranking
                    </a>
                    <a href="{{ route('pulso.index') }}"
                       class="flex items-center gap-1.5 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('pulso.*') ? 'text-amber-600' : 'text-stone-600 hover:text-stone-900' }}">
                        <x-heroicon-o-signal class="w-4 h-4" />
                        Pulso
                    </a>
                </div>
            </div>

            <!-- Search -->
            <form action="{{ route('search.index') }}" method="GET" class="hidden sm:flex items-center" role="search">
                <div class="relative">
                    <label for="search-desktop" class="sr-only">Buscar no FalaVizin</label>
                    <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" aria-hidden="true" />
                    <input
                        id="search-desktop"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Buscar..."
                        class="pl-8 pr-3 py-1.5 text-sm bg-stone-100 border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 w-48 transition-colors duration-150"
                    />
                </div>
            </form>

            {{-- Neighborhood switcher (desktop) --}}
            @php
                $displayNeighborhood = $currentNeighborhood ?? auth()->user()?->primaryNeighborhood;
            @endphp
            @if($displayNeighborhood && isset($navigationNeighborhoods))
                <div data-neighborhood-switcher-desktop class="hidden lg:block">
                    <x-neighborhood-switcher :current="$displayNeighborhood" :neighborhoods="$navigationNeighborhoods" />
                </div>
            @endif

            <!-- User Menu -->
            <div class="flex items-center gap-1 sm:gap-3">
                @auth
                    <div data-navbar-notification>
                        <livewire:notifications.notification-bell />
                    </div>
                    <a href="{{ route('feed.create') }}"
                       class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Publicar
                    </a>
                    <div class="hidden sm:block">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-stone-600 hover:text-stone-900 rounded-lg hover:bg-stone-100 transition-colors duration-150">
                                    <x-avatar :user="Auth::user()" class="w-6 h-6 text-xs" />
                                    <span>{{ Auth::user()->name }}</span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4" />
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.account')">
                                    Minha conta
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('profile.edit')">
                                    Editar perfil
                                </x-dropdown-link>
                                <button
                                    type="button"
                                    data-pwa-install
                                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                >
                                    Instalar aplicativo
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
                                    <x-dropdown-link :href="route('admin.moderation.index')">
                                        <span class="flex items-center justify-between gap-2">
                                            Moderação
                                            @if($pendingCount > 0)
                                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">
                                                    {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                                                </span>
                                            @endif
                                        </span>
                                    </x-dropdown-link>
                                    @if(Auth::user()->is_admin)
                                        <x-dropdown-link :href="route('admin.stats')">
                                            Estatísticas
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.google-places-import')">
                                            Importar Google Places
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.neighborhoods')">
                                            Bairros
                                        </x-dropdown-link>
                                    @endif
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                    >
                                        {{ __('Sair') }}
                                    </button>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:block text-sm font-medium text-stone-600 hover:text-stone-900 transition-colors duration-150">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}"
                       class="hidden sm:inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                        Cadastrar
                    </a>
                @endauth
                <button @click="open = !open"
                    class="inline-flex sm:hidden items-center justify-center p-2 rounded-md text-stone-500 hover:text-stone-700 hover:bg-stone-100 transition duration-150"
                    :aria-expanded="open.toString()"
                    aria-controls="mobile-menu"
                    aria-label="Menu de navegação">
                    <x-heroicon-o-bars-3 x-show="!open" class="h-6 w-6" />
                    <x-heroicon-o-x-mark x-show="open" class="h-6 w-6" />
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" x-transition id="mobile-menu" class="sm:hidden border-t border-stone-200">
        {{-- Neighborhood switcher (mobile) --}}
        @if($displayNeighborhood && isset($navigationNeighborhoods))
            <div data-neighborhood-switcher-mobile class="border-b border-stone-200">
                <x-neighborhood-switcher :current="$displayNeighborhood" :neighborhoods="$navigationNeighborhoods" mobile />
            </div>
        @endif

        <div class="px-4 pt-3 pb-2">
            <form action="{{ route('search.index') }}" method="GET" role="search">
                <div class="relative">
                    <label for="search-mobile" class="sr-only">Buscar no FalaVizin</label>
                    <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" aria-hidden="true" />
                    <input
                        id="search-mobile"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Buscar..."
                        class="w-full pl-8 pr-3 py-2 text-sm bg-stone-100 border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors duration-150"
                    />
                </div>
            </form>
        </div>
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('feed.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('feed.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-100' }}">
                <x-heroicon-o-newspaper class="w-5 h-5" />
                Feed
            </a>
            <a href="{{ route('businesses.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('businesses.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-100' }}">
                <x-heroicon-o-building-storefront class="w-5 h-5" />
                Serviços
            </a>
            <a href="{{ route('promotions.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('promotions.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-100' }}">
                <x-heroicon-o-tag class="w-5 h-5" />
                Promoções
            </a>
            <a href="{{ route('ranking.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('ranking.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-100' }}">
                <x-heroicon-o-trophy class="w-5 h-5" />
                Ranking
            </a>
            <a href="{{ route('pulso.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('pulso.*') ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-100' }}">
                <x-heroicon-o-signal class="w-5 h-5" />
                Pulso
            </a>
            <button
                type="button"
                data-pwa-install
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-stone-700 hover:bg-stone-100"
            >
                <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
                Instalar aplicativo
            </button>
        </div>

        <div class="px-4 py-3 border-t border-stone-200">
            @auth
                <div class="mb-2">
                    <p class="text-sm font-medium text-stone-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-stone-500">{{ Auth::user()->email }}</p>
                </div>
                <a href="{{ route('feed.create') }}"
                   class="flex items-center gap-2 px-3 py-2 mb-1 bg-amber-600 text-white rounded-lg text-sm font-medium">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Publicar
                </a>
                <a href="{{ route('profile.account') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-100">
                    Minha conta
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-100">
                    Editar perfil
                </a>
                @if(Auth::user()->is_admin || Auth::user()->isModerator())
                    <a href="{{ route('admin.moderation.index') }}"
                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-100">
                        <span class="flex items-center gap-2">
                            <x-heroicon-o-shield-check class="w-4 h-4" />
                            Moderação
                        </span>
                        @if(isset($pendingCount) && $pendingCount > 0)
                            <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">
                                {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                            </span>
                        @endif
                    </a>
                    @if(Auth::user()->is_admin)
                        <a href="{{ route('admin.stats') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-100">
                            <x-heroicon-o-chart-bar class="w-4 h-4" />
                            Estatísticas
                        </a>
                        <a href="{{ route('admin.google-places-import') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-100">
                            Importar Google Places
                        </a>
                        <a href="{{ route('admin.neighborhoods') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-100">
                            <x-heroicon-o-map-pin class="w-4 h-4" />
                            Bairros
                        </a>
                    @endif
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-100">
                        Sair
                    </button>
                </form>
            @else
                <div class="flex gap-2">
                    <a href="{{ route('login') }}"
                       class="flex-1 text-center px-4 py-2 border border-stone-300 text-stone-700 rounded-lg text-sm font-medium hover:bg-stone-50">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}"
                       class="flex-1 text-center px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">
                        Cadastrar
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>

<x-app-layout title="Pulso do Bairro" description="Veja o que está acontecendo no bairro esta semana.">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-stone-900 flex items-center gap-3" style="font-family: var(--font-display)">
                <x-heroicon-o-signal class="w-8 h-8 text-amber-600" />
                Pulso do Bairro
            </h1>
            <p class="text-stone-500 mt-1">
                Semana de {{ $weekStart->format('d/m') }} a {{ $weekEnd->format('d/m/Y') }}
            </p>
        </div>

        {{-- Métricas principais --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            {{-- Posts esta semana --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 text-center relative group">
                <p class="text-3xl font-bold text-amber-600" style="font-family: var(--font-display)">{{ $postsThisWeek }}</p>
                <p class="text-sm text-stone-500 mt-1">Posts esta semana</p>
                <div class="invisible group-hover:visible absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-stone-800 text-white text-xs rounded-lg whitespace-nowrap z-10">
                    Posts aprovados entre {{ $weekStart->format('d/m') }} e {{ $weekEnd->format('d/m') }}
                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-stone-800"></div>
                </div>
            </div>

            {{-- Problemas abertos --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 text-center relative group">
                <p class="text-3xl font-bold text-red-500" style="font-family: var(--font-display)">{{ $openProblems }}</p>
                <p class="text-sm text-stone-500 mt-1">Problemas abertos</p>
                <div class="invisible group-hover:visible absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-stone-800 text-white text-xs rounded-lg whitespace-nowrap z-10">
                    Problemas não resolvidos ({{ $inProgressCount }} em andamento)
                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-stone-800"></div>
                </div>
            </div>

            {{-- Resolvidos esta semana --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 text-center relative group">
                <p class="text-3xl font-bold text-green-600" style="font-family: var(--font-display)">{{ $resolvedThisWeek }}</p>
                <p class="text-sm text-stone-500 mt-1">Resolvidos esta semana</p>
                <div class="invisible group-hover:visible absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-stone-800 text-white text-xs rounded-lg whitespace-nowrap z-10">
                    Problemas marcados como resolvidos desde {{ $weekStart->format('d/m') }}
                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-stone-800"></div>
                </div>
            </div>

            {{-- Score do bairro --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 text-center relative group">
                @php
                    $score = $totalProblems > 0
                        ? max(1, min(10, round(10 - ($openProblems / max($totalProblems, 1)) * 5 + ($resolvedThisWeek / max($totalProblems, 1)) * 3)))
                        : 7;
                @endphp
                <p class="text-3xl font-bold {{ $score >= 7 ? 'text-green-600' : ($score >= 4 ? 'text-amber-500' : 'text-red-500') }}" style="font-family: var(--font-display)">
                    {{ $score }}/10
                </p>
                <p class="text-sm text-stone-500 mt-1">Score do bairro</p>
                <div class="invisible group-hover:visible absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-stone-800 text-white text-xs rounded-lg w-64 z-10">
                    <p class="font-semibold mb-1">Como calculamos:</p>
                    <p>Base: 10 pontos</p>
                    <p>-5 × (abertos / total) = -{{ $totalProblems > 0 ? round(($openProblems / $totalProblems) * 5, 1) : 0 }}</p>
                    <p>+3 × (resolvidos esta semana / total) = +{{ $totalProblems > 0 ? round(($resolvedThisWeek / $totalProblems) * 3, 1) : 0 }}</p>
                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-stone-800"></div>
                </div>
            </div>
        </div>

        {{-- Métricas secundárias --}}
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-stone-50 rounded-lg border border-stone-200 px-4 py-3 text-center">
                <p class="text-lg font-bold text-stone-700">{{ $resolutionRate }}%</p>
                <p class="text-xs text-stone-500">Taxa de resolução</p>
            </div>
            <div class="bg-stone-50 rounded-lg border border-stone-200 px-4 py-3 text-center">
                <p class="text-lg font-bold text-stone-700">{{ $requestsCount }}</p>
                <p class="text-xs text-stone-500">Pedidos esta semana</p>
            </div>
            <div class="bg-stone-50 rounded-lg border border-stone-200 px-4 py-3 text-center">
                <p class="text-lg font-bold text-stone-700">{{ $activeBusinesses }}</p>
                <p class="text-xs text-stone-500">Negócios ativos</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">

            {{-- Coluna principal --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Atividade por categoria --}}
                @if($postsByCategory->isNotEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-6">
                        <h2 class="text-base font-semibold text-stone-800 mb-4 flex items-center gap-2">
                            <x-heroicon-o-chart-bar class="w-4 h-4 text-amber-500" />
                            Atividade por categoria esta semana
                        </h2>
                        @php $maxCount = $postsByCategory->max(); @endphp
                        <div class="space-y-3">
                            @foreach($postsByCategory as $categoryName => $count)
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-stone-600 w-28 shrink-0 truncate">{{ $categoryName ?? 'Sem categoria' }}</span>
                                    <div class="flex-1 bg-stone-100 rounded-full h-2.5 overflow-hidden">
                                        <div
                                            class="h-2.5 bg-amber-500 rounded-full transition-all duration-500"
                                            style="width: {{ $maxCount > 0 ? round(($count / $maxCount) * 100) : 0 }}%"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-semibold text-stone-700 w-6 text-right shrink-0">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Problemas mais votados (abertos) --}}
                @if($topProblems->isNotEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-6">
                        <h2 class="text-base font-semibold text-stone-800 mb-4 flex items-center gap-2">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500" />
                            Problemas em aberto mais votados
                        </h2>
                        <div class="space-y-3">
                            @foreach($topProblems as $problem)
                                <a href="{{ route('feed.show', $problem) }}"
                                   class="flex items-center gap-3 p-3 rounded-lg border border-stone-100 hover:border-stone-200 hover:bg-stone-50 transition-colors duration-150 group">
                                    <div class="shrink-0 flex flex-col items-center justify-center w-10 h-10 rounded-lg bg-red-50 border border-red-100 text-center">
                                        <span class="text-sm font-bold text-red-600">{{ $problem->votes_count }}</span>
                                        <span class="text-xs text-red-400 leading-none">votos</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-stone-900 group-hover:text-amber-700 transition-colors line-clamp-1">
                                            {{ $problem->title }}
                                        </p>
                                        <p class="text-xs text-stone-400">{{ $problem->user->name }} · {{ $problem->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if($problem->resolution_status?->value === 'em_andamento')
                                        <span class="shrink-0 inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full">
                                            <x-heroicon-o-arrow-path class="w-3 h-3" />
                                            Em andamento
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Pedidos com mais engajamento --}}
                @if($activeRequests->isNotEmpty())
                    <div class="bg-white rounded-xl border border-blue-100 p-6">
                        <h2 class="text-base font-semibold text-stone-800 mb-4 flex items-center gap-2">
                            <x-heroicon-o-hand-raised class="w-4 h-4 text-blue-500" />
                            Pedidos com mais engajamento (7 dias)
                        </h2>
                        <div class="space-y-3">
                            @foreach($activeRequests as $request)
                                <a href="{{ route('feed.show', $request) }}"
                                   class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 border border-blue-100 hover:border-blue-200 transition-colors duration-150 group">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-stone-900 group-hover:text-blue-700 transition-colors line-clamp-1">
                                            {{ $request->title }}
                                        </p>
                                        <p class="text-xs text-stone-400">{{ $request->user->name }} · {{ $request->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center gap-1 text-xs text-stone-500">
                                        <x-heroicon-o-chat-bubble-left class="w-3.5 h-3.5" />
                                        {{ $request->comments_count }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3 text-right">
                            <a href="{{ route('categories.show', 'pedido') }}" class="text-xs font-medium text-amber-600 hover:text-amber-700">
                                Ver todos os pedidos →
                            </a>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Negócios mais bem avaliados --}}
                @if($topBusiness->isNotEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-5">
                        <h2 class="text-sm font-semibold text-stone-700 mb-4 flex items-center gap-2">
                            <x-heroicon-s-star class="w-4 h-4 text-amber-400" />
                            Mais bem avaliados
                        </h2>
                        <div class="space-y-3">
                            @foreach($topBusiness as $i => $business)
                                <a href="{{ route('businesses.show', $business) }}"
                                   class="flex items-center gap-3 group">
                                    <span class="text-lg font-bold text-stone-200 w-5 shrink-0">{{ $i + 1 }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-stone-900 group-hover:text-amber-700 transition-colors truncate">
                                            {{ $business->name }}
                                        </p>
                                        <p class="text-xs text-stone-400 truncate">{{ $business->category?->name }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs text-stone-400">
                                        {{ $business->positive_reviews_count }}
                                        <x-heroicon-s-star class="w-3 h-3 text-amber-400 inline" />
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-3 border-t border-stone-100">
                            <a href="{{ route('businesses.index') }}" class="text-xs font-medium text-amber-600 hover:text-amber-700">
                                Ver todos os serviços →
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Legenda --}}
                <div class="bg-stone-50 rounded-xl border border-stone-200 p-5">
                    <h3 class="text-xs font-semibold text-stone-600 uppercase tracking-wider mb-3">Como ler os dados</h3>
                    <div class="space-y-2.5 text-xs text-stone-500">
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 shrink-0"></span>
                            <p><strong class="text-stone-700">Posts esta semana:</strong> publicações aprovadas nos últimos 7 dias</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500 mt-1 shrink-0"></span>
                            <p><strong class="text-stone-700">Problemas abertos:</strong> não resolvidos (inclui "em andamento")</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 mt-1 shrink-0"></span>
                            <p><strong class="text-stone-700">Resolvidos:</strong> marcados como resolvidos esta semana</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500 mt-1 shrink-0"></span>
                            <p><strong class="text-stone-700">Taxa de resolução:</strong> (resolvidos / total de problemas) × 100</p>
                        </div>
                    </div>
                </div>

                {{-- Dica de engajamento --}}
                <div class="bg-amber-50 rounded-xl border border-amber-200 p-5">
                    <x-heroicon-o-light-bulb class="w-5 h-5 text-amber-600 mb-2" />
                    <p class="text-sm font-semibold text-stone-800 mb-1">Quer ver o bairro melhorar?</p>
                    <p class="text-xs text-stone-600 mb-3">Registre problemas, faça pedidos e vote nas questões que mais te importam.</p>
                    @auth
                        <a href="{{ route('feed.create') }}"
                           class="inline-flex items-center gap-1.5 text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 px-3 py-1.5 rounded-lg transition-colors duration-150">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                            Publicar no feed
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-1.5 text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 px-3 py-1.5 rounded-lg transition-colors duration-150">
                            Entrar para participar
                        </a>
                    @endauth
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

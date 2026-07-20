<x-app-layout title="Moderação">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Moderação</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.sponsored-posts.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-lg transition-colors duration-150">
                    <x-heroicon-s-bolt class="w-4 h-4" />
                    Posts Patrocinados
                </a>
                <a href="{{ route('admin.google-places-import') }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-lg transition-colors duration-150">
                    <x-heroicon-o-map-pin class="w-4 h-4" />
                    Importar Google Places
                </a>
            </div>
        </div>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        @php
            $totalPending = $pendingPosts->total() + $pendingBusinesses->total() + $pendingPromotions->total();
            $totalReported = $reportedPosts->total() + $reportedBusinesses->total() + $reportedPromotions->total();
        @endphp

        @if($totalPending === 0 && $totalReported === 0 && $pendingUpgrades->isEmpty() && $pendingClaims->isEmpty())
            <div class="bg-white rounded-xl border border-stone-200 p-10 text-center">
                <x-heroicon-o-shield-check class="w-10 h-10 text-green-400 mx-auto mb-3" />
                <p class="text-stone-500">Nenhum conteúdo pendente ou reportado.</p>
            </div>
        @endif

        {{-- Reivindicações de negócio --}}
        @if($pendingClaims->isNotEmpty())
            <div class="mb-10">
                <h2 class="text-base font-semibold text-amber-600 uppercase tracking-wide mb-4">
                    Reivindicações de negócio ({{ $pendingClaims->count() }})
                </h2>
                <div class="space-y-2">
                    @foreach($pendingClaims as $business)
                        <div class="bg-white rounded-xl border border-amber-200 p-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-medium text-stone-900">{{ $business->name }}</p>
                                <p class="text-xs text-stone-500 mt-0.5">
                                    Solicitante: {{ $business->claimUser->name }} ({{ $business->claimUser->email }})
                                </p>
                                <p class="text-xs text-stone-400 mt-0.5">
                                    {{ $business->neighborhood }} · Solicitado {{ $business->claim_requested_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('businesses.show', $business) }}"
                                   target="_blank"
                                   class="px-3 py-1.5 text-stone-600 bg-stone-100 hover:bg-stone-200 text-xs font-medium rounded-lg transition-colors inline-flex items-center gap-1">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                    Ver
                                </a>
                                <form action="{{ route('admin.claims.approve', $business) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Aprovar
                                    </button>
                                </form>
                                <form action="{{ route('admin.claims.reject', $business) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Rejeitar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Solicitações de upgrade de plano --}}
        @if($pendingUpgrades->isNotEmpty())
            <div class="mb-10">
                <h2 class="text-base font-semibold text-amber-600 uppercase tracking-wide mb-4">
                    Upgrades de plano solicitados ({{ $pendingUpgrades->count() }})
                </h2>
                <div class="space-y-2">
                    @foreach($pendingUpgrades as $business)
                        <div class="bg-white rounded-xl border border-amber-200 p-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-medium text-stone-900">{{ $business->name }}</p>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-600">
                                        Plano: Free → Destaque
                                    </span>
                                </div>
                                <p class="text-xs text-stone-400 mt-0.5">
                                    {{ $business->neighborhood }}
                                    {{ $business->user ? ' · ' . $business->user->name : '' }}
                                    · Solicitado {{ $business->plan_upgrade_requested_at->diffForHumans() }}
                                </p>
                                @if($business->description)
                                    <p class="text-sm text-stone-600 mt-1 line-clamp-2">{{ $business->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('businesses.show', $business) }}"
                                   target="_blank"
                                   class="px-3 py-1.5 text-stone-600 bg-stone-100 hover:bg-stone-200 text-xs font-medium rounded-lg transition-colors inline-flex items-center gap-1">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                    Ver
                                </a>
                                <form action="{{ route('admin.businesses.upgrade.approve', $business) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Aprovar upgrade
                                    </button>
                                </form>
                                <form action="{{ route('admin.businesses.upgrade.dismiss', $business) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-stone-200 hover:bg-stone-300 text-stone-700 text-xs font-medium rounded-lg transition-colors">
                                        Dispensar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pendentes de aprovação --}}
        @if($totalPending > 0)
            <div class="mb-10">
                <h2 class="text-base font-semibold text-stone-500 uppercase tracking-wide mb-4">
                    Aguardando aprovação ({{ $totalPending }})
                </h2>

                @if($pendingPosts->isNotEmpty())
                    <div
                        x-data="{ selected: [], allIds: {{ $pendingPosts->pluck('id') }} }"
                        class="mb-6"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-stone-700 flex items-center gap-1.5">
                                <x-heroicon-o-newspaper class="w-4 h-4 text-stone-400" /> Posts
                            </h3>
                            <label class="flex items-center gap-1.5 text-xs text-stone-500 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                                    @change="selected = $event.target.checked ? [...allIds] : []"
                                    :checked="selected.length === allIds.length && allIds.length > 0"
                                />
                                Selecionar todos
                            </label>
                        </div>

                        <div class="space-y-2 mb-3">
                            @foreach($pendingPosts as $post)
                                @include('admin.moderation._item', ['type' => 'post', 'model' => $post, 'title' => $post->title, 'meta' => $post->user->name . ' · ' . $post->created_at->diffForHumans(), 'excerpt' => $post->body, 'selectable' => true])
                            @endforeach
                        </div>

                        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-2 mt-2 p-3 bg-stone-50 border border-stone-200 rounded-lg">
                            <span class="text-xs text-stone-500 mr-1" x-text="selected.length + ' selecionado(s)'"></span>
                            <form :action="'{{ route('admin.moderation.bulk') }}'" method="POST" @submit.prevent="submitBulk($el, 'post', 'approve')">
                                @csrf
                                <input type="hidden" name="action" value="approve" />
                                <input type="hidden" name="type" value="post" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Aprovar selecionados
                                </button>
                            </form>
                            <form :action="'{{ route('admin.moderation.bulk') }}'" method="POST" @submit.prevent="submitBulk($el, 'post', 'reject')">
                                @csrf
                                <input type="hidden" name="action" value="reject" />
                                <input type="hidden" name="type" value="post" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Rejeitar selecionados
                                </button>
                            </form>
                        </div>

                        @if($pendingPosts->hasPages())
                            <div class="mt-3">{{ $pendingPosts->links() }}</div>
                        @endif
                    </div>
                @endif

                @if($pendingBusinesses->isNotEmpty())
                    <div
                        x-data="{ selected: [], allIds: {{ $pendingBusinesses->pluck('id') }} }"
                        class="mb-6"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-stone-700 flex items-center gap-1.5">
                                <x-heroicon-o-building-storefront class="w-4 h-4 text-stone-400" /> Negócios
                            </h3>
                            <label class="flex items-center gap-1.5 text-xs text-stone-500 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                                    @change="selected = $event.target.checked ? [...allIds] : []"
                                    :checked="selected.length === allIds.length && allIds.length > 0"
                                />
                                Selecionar todos
                            </label>
                        </div>

                        <div class="space-y-2 mb-3">
                            @foreach($pendingBusinesses as $business)
                                @include('admin.moderation._item', ['type' => 'business', 'model' => $business, 'title' => $business->name, 'meta' => $business->neighborhood . ($business->user ? ' · ' . $business->user->name : '') . ' · ' . $business->created_at->diffForHumans(), 'excerpt' => $business->description, 'selectable' => true])
                            @endforeach
                        </div>

                        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-2 mt-2 p-3 bg-stone-50 border border-stone-200 rounded-lg">
                            <span class="text-xs text-stone-500 mr-1" x-text="selected.length + ' selecionado(s)'"></span>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve" />
                                <input type="hidden" name="type" value="business" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Aprovar selecionados
                                </button>
                            </form>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject" />
                                <input type="hidden" name="type" value="business" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Rejeitar selecionados
                                </button>
                            </form>
                        </div>

                        @if($pendingBusinesses->hasPages())
                            <div class="mt-3">{{ $pendingBusinesses->links() }}</div>
                        @endif
                    </div>
                @endif

                @if($pendingPromotions->isNotEmpty())
                    <div
                        x-data="{ selected: [], allIds: {{ $pendingPromotions->pluck('id') }} }"
                        class="mb-6"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-stone-700 flex items-center gap-1.5">
                                <x-heroicon-o-tag class="w-4 h-4 text-stone-400" /> Promoções
                            </h3>
                            <label class="flex items-center gap-1.5 text-xs text-stone-500 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                                    @change="selected = $event.target.checked ? [...allIds] : []"
                                    :checked="selected.length === allIds.length && allIds.length > 0"
                                />
                                Selecionar todos
                            </label>
                        </div>

                        <div class="space-y-2 mb-3">
                            @foreach($pendingPromotions as $promotion)
                                @include('admin.moderation._item', ['type' => 'promotion', 'model' => $promotion, 'title' => $promotion->title, 'meta' => $promotion->business->name . ' · ' . $promotion->created_at->diffForHumans(), 'excerpt' => $promotion->description, 'selectable' => true])
                            @endforeach
                        </div>

                        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-2 mt-2 p-3 bg-stone-50 border border-stone-200 rounded-lg">
                            <span class="text-xs text-stone-500 mr-1" x-text="selected.length + ' selecionado(s)'"></span>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve" />
                                <input type="hidden" name="type" value="promotion" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Aprovar selecionados
                                </button>
                            </form>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject" />
                                <input type="hidden" name="type" value="promotion" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Rejeitar selecionados
                                </button>
                            </form>
                        </div>

                        @if($pendingPromotions->hasPages())
                            <div class="mt-3">{{ $pendingPromotions->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- Reportados --}}
        @if($totalReported > 0)
            <div>
                <h2 class="text-base font-semibold text-red-500 uppercase tracking-wide mb-4">
                    Reportados ({{ $totalReported }})
                </h2>

                @if($reportedPosts->isNotEmpty())
                    <div
                        x-data="{ selected: [], allIds: {{ $reportedPosts->pluck('id') }} }"
                        class="mb-6"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-stone-700 flex items-center gap-1.5">
                                <x-heroicon-o-newspaper class="w-4 h-4 text-stone-400" /> Posts
                            </h3>
                            <label class="flex items-center gap-1.5 text-xs text-stone-500 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                                    @change="selected = $event.target.checked ? [...allIds] : []"
                                    :checked="selected.length === allIds.length && allIds.length > 0"
                                />
                                Selecionar todos
                            </label>
                        </div>

                        <div class="space-y-2 mb-3">
                            @foreach($reportedPosts as $post)
                                @include('admin.moderation._item', ['type' => 'post', 'model' => $post, 'title' => $post->title, 'meta' => 'Reportado ' . $post->reported_at->diffForHumans() . ' · ' . $post->user->name, 'excerpt' => $post->body, 'reported' => true, 'selectable' => true])
                            @endforeach
                        </div>

                        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-2 mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <span class="text-xs text-stone-500 mr-1" x-text="selected.length + ' selecionado(s)'"></span>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve" />
                                <input type="hidden" name="type" value="post" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Manter (aprovar)
                                </button>
                            </form>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject" />
                                <input type="hidden" name="type" value="post" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Remover (rejeitar)
                                </button>
                            </form>
                        </div>

                        @if($reportedPosts->hasPages())
                            <div class="mt-3">{{ $reportedPosts->links() }}</div>
                        @endif
                    </div>
                @endif

                @if($reportedBusinesses->isNotEmpty())
                    <div
                        x-data="{ selected: [], allIds: {{ $reportedBusinesses->pluck('id') }} }"
                        class="mb-6"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-stone-700 flex items-center gap-1.5">
                                <x-heroicon-o-building-storefront class="w-4 h-4 text-stone-400" /> Negócios
                            </h3>
                            <label class="flex items-center gap-1.5 text-xs text-stone-500 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                                    @change="selected = $event.target.checked ? [...allIds] : []"
                                    :checked="selected.length === allIds.length && allIds.length > 0"
                                />
                                Selecionar todos
                            </label>
                        </div>

                        <div class="space-y-2 mb-3">
                            @foreach($reportedBusinesses as $business)
                                @include('admin.moderation._item', ['type' => 'business', 'model' => $business, 'title' => $business->name, 'meta' => 'Reportado ' . $business->reported_at->diffForHumans() . ' · ' . $business->neighborhood, 'excerpt' => $business->description, 'reported' => true, 'selectable' => true])
                            @endforeach
                        </div>

                        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-2 mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <span class="text-xs text-stone-500 mr-1" x-text="selected.length + ' selecionado(s)'"></span>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve" />
                                <input type="hidden" name="type" value="business" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Manter (aprovar)
                                </button>
                            </form>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject" />
                                <input type="hidden" name="type" value="business" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Remover (rejeitar)
                                </button>
                            </form>
                        </div>

                        @if($reportedBusinesses->hasPages())
                            <div class="mt-3">{{ $reportedBusinesses->links() }}</div>
                        @endif
                    </div>
                @endif

                @if($reportedPromotions->isNotEmpty())
                    <div
                        x-data="{ selected: [], allIds: {{ $reportedPromotions->pluck('id') }} }"
                        class="mb-6"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-stone-700 flex items-center gap-1.5">
                                <x-heroicon-o-tag class="w-4 h-4 text-stone-400" /> Promoções
                            </h3>
                            <label class="flex items-center gap-1.5 text-xs text-stone-500 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                                    @change="selected = $event.target.checked ? [...allIds] : []"
                                    :checked="selected.length === allIds.length && allIds.length > 0"
                                />
                                Selecionar todos
                            </label>
                        </div>

                        <div class="space-y-2 mb-3">
                            @foreach($reportedPromotions as $promotion)
                                @include('admin.moderation._item', ['type' => 'promotion', 'model' => $promotion, 'title' => $promotion->title, 'meta' => 'Reportado ' . $promotion->reported_at->diffForHumans() . ' · ' . $promotion->business->name, 'excerpt' => $promotion->description, 'reported' => true, 'selectable' => true])
                            @endforeach
                        </div>

                        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-2 mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <span class="text-xs text-stone-500 mr-1" x-text="selected.length + ' selecionado(s)'"></span>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve" />
                                <input type="hidden" name="type" value="promotion" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Manter (aprovar)
                                </button>
                            </form>
                            <form action="{{ route('admin.moderation.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject" />
                                <input type="hidden" name="type" value="promotion" />
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="ids[]" :value="id" />
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Remover (rejeitar)
                                </button>
                            </form>
                        </div>

                        @if($reportedPromotions->hasPages())
                            <div class="mt-3">{{ $reportedPromotions->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>

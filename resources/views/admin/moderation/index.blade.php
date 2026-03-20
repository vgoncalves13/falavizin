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

        @if($totalPending === 0 && $totalReported === 0)
            <div class="bg-white rounded-xl border border-stone-200 p-10 text-center">
                <x-heroicon-o-shield-check class="w-10 h-10 text-green-400 mx-auto mb-3" />
                <p class="text-stone-500">Nenhum conteúdo pendente ou reportado.</p>
            </div>
        @endif

        {{-- Pendentes de aprovação --}}
        @if($totalPending > 0)
            <div class="mb-10">
                <h2 class="text-base font-semibold text-stone-500 uppercase tracking-wide mb-4">
                    Aguardando aprovação ({{ $totalPending }})
                </h2>

                @if($pendingPosts->isNotEmpty())
                    <h3 class="text-sm font-semibold text-stone-700 mb-2 flex items-center gap-1.5">
                        <x-heroicon-o-newspaper class="w-4 h-4 text-stone-400" /> Posts
                    </h3>
                    <div class="space-y-2 mb-3">
                        @foreach($pendingPosts as $post)
                            @include('admin.moderation._item', ['type' => 'post', 'model' => $post, 'title' => $post->title, 'meta' => $post->user->name . ' · ' . $post->created_at->diffForHumans(), 'excerpt' => $post->body])
                        @endforeach
                    </div>
                    @if($pendingPosts->hasPages())
                        <div class="mb-5">{{ $pendingPosts->links() }}</div>
                    @else
                        <div class="mb-5"></div>
                    @endif
                @endif

                @if($pendingBusinesses->isNotEmpty())
                    <h3 class="text-sm font-semibold text-stone-700 mb-2 flex items-center gap-1.5">
                        <x-heroicon-o-building-storefront class="w-4 h-4 text-stone-400" /> Negócios
                    </h3>
                    <div class="space-y-2 mb-3">
                        @foreach($pendingBusinesses as $business)
                            @include('admin.moderation._item', ['type' => 'business', 'model' => $business, 'title' => $business->name, 'meta' => $business->neighborhood . ($business->user ? ' · ' . $business->user->name : '') . ' · ' . $business->created_at->diffForHumans(), 'excerpt' => $business->description])
                        @endforeach
                    </div>
                    @if($pendingBusinesses->hasPages())
                        <div class="mb-5">{{ $pendingBusinesses->links() }}</div>
                    @else
                        <div class="mb-5"></div>
                    @endif
                @endif

                @if($pendingPromotions->isNotEmpty())
                    <h3 class="text-sm font-semibold text-stone-700 mb-2 flex items-center gap-1.5">
                        <x-heroicon-o-tag class="w-4 h-4 text-stone-400" /> Promoções
                    </h3>
                    <div class="space-y-2 mb-3">
                        @foreach($pendingPromotions as $promotion)
                            @include('admin.moderation._item', ['type' => 'promotion', 'model' => $promotion, 'title' => $promotion->title, 'meta' => $promotion->business->name . ' · ' . $promotion->created_at->diffForHumans(), 'excerpt' => $promotion->description])
                        @endforeach
                    </div>
                    @if($pendingPromotions->hasPages())
                        <div class="mb-5">{{ $pendingPromotions->links() }}</div>
                    @else
                        <div class="mb-5"></div>
                    @endif
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
                    <h3 class="text-sm font-semibold text-stone-700 mb-2 flex items-center gap-1.5">
                        <x-heroicon-o-newspaper class="w-4 h-4 text-stone-400" /> Posts
                    </h3>
                    <div class="space-y-2 mb-3">
                        @foreach($reportedPosts as $post)
                            @include('admin.moderation._item', ['type' => 'post', 'model' => $post, 'title' => $post->title, 'meta' => 'Reportado ' . $post->reported_at->diffForHumans() . ' · ' . $post->user->name, 'excerpt' => $post->body, 'reported' => true])
                        @endforeach
                    </div>
                    @if($reportedPosts->hasPages())
                        <div class="mb-5">{{ $reportedPosts->links() }}</div>
                    @else
                        <div class="mb-5"></div>
                    @endif
                @endif

                @if($reportedBusinesses->isNotEmpty())
                    <h3 class="text-sm font-semibold text-stone-700 mb-2 flex items-center gap-1.5">
                        <x-heroicon-o-building-storefront class="w-4 h-4 text-stone-400" /> Negócios
                    </h3>
                    <div class="space-y-2 mb-3">
                        @foreach($reportedBusinesses as $business)
                            @include('admin.moderation._item', ['type' => 'business', 'model' => $business, 'title' => $business->name, 'meta' => 'Reportado ' . $business->reported_at->diffForHumans() . ' · ' . $business->neighborhood, 'excerpt' => $business->description, 'reported' => true])
                        @endforeach
                    </div>
                    @if($reportedBusinesses->hasPages())
                        <div class="mb-5">{{ $reportedBusinesses->links() }}</div>
                    @else
                        <div class="mb-5"></div>
                    @endif
                @endif

                @if($reportedPromotions->isNotEmpty())
                    <h3 class="text-sm font-semibold text-stone-700 mb-2 flex items-center gap-1.5">
                        <x-heroicon-o-tag class="w-4 h-4 text-stone-400" /> Promoções
                    </h3>
                    <div class="space-y-2 mb-3">
                        @foreach($reportedPromotions as $promotion)
                            @include('admin.moderation._item', ['type' => 'promotion', 'model' => $promotion, 'title' => $promotion->title, 'meta' => 'Reportado ' . $promotion->reported_at->diffForHumans() . ' · ' . $promotion->business->name, 'excerpt' => $promotion->description, 'reported' => true])
                        @endforeach
                    </div>
                    @if($reportedPromotions->hasPages())
                        <div class="mb-5">{{ $reportedPromotions->links() }}</div>
                    @else
                        <div class="mb-5"></div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</x-app-layout>

<x-app-layout title="Moderação">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-stone-900 mb-6" style="font-family: var(--font-display)">Moderação</h1>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        @php $totalPending = $pendingPosts->count() + $pendingBusinesses->count() + $pendingPromotions->count(); @endphp

        @if($totalPending === 0)
            <div class="bg-white rounded-xl border border-stone-200 p-10 text-center">
                <x-heroicon-o-shield-check class="w-10 h-10 text-green-400 mx-auto mb-3" />
                <p class="text-stone-500">Nenhum conteúdo pendente de moderação.</p>
            </div>
        @endif

        {{-- Posts pendentes --}}
        @if($pendingPosts->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-stone-900 mb-3 flex items-center gap-2">
                    <x-heroicon-o-newspaper class="w-5 h-5 text-stone-400" />
                    Posts <span class="text-sm font-normal text-stone-400">({{ $pendingPosts->count() }})</span>
                </h2>
                <div class="space-y-2">
                    @foreach($pendingPosts as $post)
                        <div class="bg-white rounded-xl border border-stone-200 p-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-medium text-stone-900 truncate">{{ $post->title }}</p>
                                <p class="text-xs text-stone-400 mt-0.5">
                                    {{ $post->user->name }} · {{ $post->created_at->diffForHumans() }}
                                    <x-category-badge :category="$post->category" />
                                </p>
                                <p class="text-sm text-stone-600 mt-1 line-clamp-2">{{ $post->body }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.moderation.approve', ['type' => 'post', 'id' => $post->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Aprovar
                                    </button>
                                </form>
                                <form action="{{ route('admin.moderation.reject', ['type' => 'post', 'id' => $post->id]) }}" method="POST">
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

        {{-- Negócios pendentes --}}
        @if($pendingBusinesses->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-stone-900 mb-3 flex items-center gap-2">
                    <x-heroicon-o-building-storefront class="w-5 h-5 text-stone-400" />
                    Negócios <span class="text-sm font-normal text-stone-400">({{ $pendingBusinesses->count() }})</span>
                </h2>
                <div class="space-y-2">
                    @foreach($pendingBusinesses as $business)
                        <div class="bg-white rounded-xl border border-stone-200 p-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-medium text-stone-900 truncate">{{ $business->name }}</p>
                                <p class="text-xs text-stone-400 mt-0.5">
                                    {{ $business->neighborhood }}
                                    @if($business->user) · {{ $business->user->name }} @endif
                                    · {{ $business->created_at->diffForHumans() }}
                                </p>
                                @if($business->description)
                                    <p class="text-sm text-stone-600 mt-1 line-clamp-2">{{ $business->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.moderation.approve', ['type' => 'business', 'id' => $business->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Aprovar
                                    </button>
                                </form>
                                <form action="{{ route('admin.moderation.reject', ['type' => 'business', 'id' => $business->id]) }}" method="POST">
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

        {{-- Promoções pendentes --}}
        @if($pendingPromotions->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-stone-900 mb-3 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-5 h-5 text-stone-400" />
                    Promoções <span class="text-sm font-normal text-stone-400">({{ $pendingPromotions->count() }})</span>
                </h2>
                <div class="space-y-2">
                    @foreach($pendingPromotions as $promotion)
                        <div class="bg-white rounded-xl border border-stone-200 p-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-medium text-stone-900 truncate">{{ $promotion->title }}</p>
                                <p class="text-xs text-stone-400 mt-0.5">
                                    {{ $promotion->business->name }} · {{ $promotion->created_at->diffForHumans() }}
                                </p>
                                @if($promotion->description)
                                    <p class="text-sm text-stone-600 mt-1 line-clamp-2">{{ $promotion->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.moderation.approve', ['type' => 'promotion', 'id' => $promotion->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Aprovar
                                    </button>
                                </form>
                                <form action="{{ route('admin.moderation.reject', ['type' => 'promotion', 'id' => $promotion->id]) }}" method="POST">
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
    </div>
</x-app-layout>

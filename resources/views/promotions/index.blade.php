<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-stone-900 mb-6" style="font-family: var(--font-display)">Promoções</h1>

        @if($promotions->isEmpty())
            <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                <x-heroicon-o-tag class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                <p class="text-stone-500">Nenhuma promoção ativa no momento.</p>
                <p class="text-sm text-stone-400 mt-1">Fique de olho — comerciantes publicam promoções por aqui!</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($promotions as $promotion)
                    <a href="{{ route('businesses.show', $promotion->business) }}"
                       class="group block bg-white rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700">
                                <x-heroicon-o-tag class="w-3.5 h-3.5" />
                                {{ $promotion->business->name }}
                            </span>
                            @if($promotion->ends_at)
                                <span class="text-xs text-stone-400 shrink-0">
                                    até {{ $promotion->ends_at->format('d/m') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="font-semibold text-stone-900 leading-snug group-hover:text-amber-700 transition-colors mb-1">
                            {{ $promotion->title }}
                        </h3>
                        @if($promotion->description)
                            <p class="text-sm text-stone-600 line-clamp-2">{{ $promotion->description }}</p>
                        @endif
                        <div class="mt-3 flex items-center gap-1 text-xs text-stone-400">
                            <x-heroicon-o-map-pin class="w-3 h-3" />
                            {{ $promotion->business->neighborhood }}
                            <x-category-badge :category="$promotion->business->category" />
                        </div>
                    </a>
                @endforeach
            </div>

            @if($promotions->hasPages())
                <div class="mt-8">
                    {{ $promotions->links() }}
                </div>
            @endif
        @endif
    </div>
</x-app-layout>

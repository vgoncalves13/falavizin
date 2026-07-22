<div>
    @if($post->category?->slug === 'pedido' && $post->interests->isNotEmpty())
        <div class="mt-6 bg-white rounded-xl border border-blue-200 p-5">
            <h2 class="text-base font-semibold text-stone-700 mb-3 flex items-center gap-2">
                <x-heroicon-o-hand-raised class="w-4 h-4 text-blue-500" />
                Profissionais interessados ({{ $post->interests->count() }})
            </h2>
            <div class="space-y-3">
                @foreach($post->interests as $merchant)
                    <div class="flex items-center justify-between gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-blue-700">{{ substr($merchant->name, 0, 1) }}</span>
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('users.show', $merchant) }}"
                                   class="text-sm font-medium text-stone-900 hover:text-amber-600 transition-colors">
                                    {{ $merchant->name }}
                                </a>
                                @if($merchant->pivot->message)
                                    <p class="text-xs text-stone-500 mt-0.5 line-clamp-1">{{ $merchant->pivot->message }}</p>
                                @endif
                            </div>
                        </div>
                        @if($merchant->businesses->isNotEmpty())
                            <a href="{{ route('businesses.show', $merchant->businesses->first()) }}"
                               class="shrink-0 text-xs text-amber-600 hover:text-amber-700 font-medium">
                                Ver negócio
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

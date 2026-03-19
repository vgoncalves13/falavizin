<div>
    {{-- Cabeçalho: média e total --}}
    <div class="flex items-center gap-4 mb-5">
        <h2 class="text-lg font-semibold text-stone-900">Avaliações</h2>
        @if($averageRating)
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($averageRating))
                            <x-heroicon-s-star class="w-4 h-4 text-amber-400" />
                        @else
                            <x-heroicon-o-star class="w-4 h-4 text-stone-300" />
                        @endif
                    @endfor
                </div>
                <span class="text-sm font-semibold text-stone-800">{{ number_format($averageRating, 1, ',') }}</span>
                <span class="text-xs text-stone-400">({{ $reviews->count() }} {{ Str::plural('avaliação', $reviews->count()) }})</span>
            </div>
        @else
            <span class="text-sm text-stone-400">Sem avaliações ainda.</span>
        @endif
    </div>

    {{-- Formulário --}}
    @auth
        <div class="bg-stone-50 rounded-xl border border-stone-200 p-4 mb-5">
            <p class="text-sm font-medium text-stone-700 mb-3">
                {{ $userReview ? 'Sua avaliação' : 'Avaliar este negócio' }}
            </p>

            {{-- Star picker --}}
            <div
                x-data="{ hovered: 0, get active() { return this.hovered || $wire.rating; } }"
                class="flex items-center gap-1 mb-3"
            >
                <template x-for="i in [1,2,3,4,5]" :key="i">
                    <button
                        type="button"
                        @mouseenter="hovered = i"
                        @mouseleave="hovered = 0"
                        @click="$wire.set('rating', i)"
                        class="transition-transform hover:scale-110 focus:outline-none"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-7 h-7 transition-colors"
                             :fill="active >= i ? '#fbbf24' : 'none'"
                             :stroke="active >= i ? 'none' : '#d6d3d1'"
                             stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                        </svg>
                    </button>
                </template>
                <span x-show="$wire.rating > 0"
                      x-text="['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'][$wire.rating]"
                      class="ml-2 text-xs text-stone-400"></span>
            </div>
            @error('rating') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <textarea
                wire:model="body"
                rows="2"
                placeholder="Conte sua experiência (opcional)..."
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 resize-none"
            ></textarea>
            @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="mt-2 flex items-center justify-between">
                @if($userReview)
                    <button
                        wire:click="deleteReview({{ $userReview->id }})"
                        wire:confirm="Remover sua avaliação?"
                        type="button"
                        class="text-xs text-stone-400 hover:text-red-500 transition-colors"
                    >
                        Remover avaliação
                    </button>
                @else
                    <span></span>
                @endif
                <button
                    wire:click="saveReview"
                    wire:loading.attr="disabled"
                    type="button"
                    class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <span wire:loading.remove wire:target="saveReview">{{ $userReview ? 'Atualizar' : 'Publicar' }}</span>
                    <span wire:loading wire:target="saveReview">Salvando...</span>
                </button>
            </div>
        </div>
    @else
        <div class="mb-5 p-4 bg-stone-50 rounded-lg border border-stone-200 text-center">
            <p class="text-sm text-stone-500">
                <a href="{{ route('login') }}" class="text-amber-600 font-medium hover:text-amber-700">Entre</a>
                para avaliar este negócio.
            </p>
        </div>
    @endauth

    {{-- Lista de avaliações --}}
    @if($reviews->isNotEmpty())
        <div class="space-y-3">
            @foreach($reviews as $review)
                <div class="bg-white rounded-lg border border-stone-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-amber-700">{{ substr($review->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <a href="{{ route('users.show', $review->user) }}"
                                   class="text-sm font-medium text-stone-900 hover:text-amber-700 transition-colors">{{ $review->user->name }}</a>
                                <div class="flex items-center gap-0.5 mt-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <x-heroicon-s-star class="w-3.5 h-3.5 text-amber-400" />
                                        @else
                                            <x-heroicon-o-star class="w-3.5 h-3.5 text-stone-200" />
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs text-stone-400">{{ $review->created_at->diffForHumans() }}</span>
                            @can('delete', $review)
                                <button
                                    wire:click="deleteReview({{ $review->id }})"
                                    wire:confirm="Remover esta avaliação?"
                                    class="text-stone-300 hover:text-red-500 transition-colors"
                                >
                                    <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                </button>
                            @endcan
                        </div>
                    </div>
                    @if($review->body)
                        <p class="mt-2 text-sm text-stone-600">{{ $review->body }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

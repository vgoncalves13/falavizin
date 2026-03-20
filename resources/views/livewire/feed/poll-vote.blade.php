<div class="mt-5 border border-stone-200 rounded-xl p-4 bg-stone-50">
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm font-semibold text-stone-800 flex items-center gap-1.5">
            <x-heroicon-o-chart-bar-square class="w-4 h-4 text-amber-600" />
            {{ $poll->question }}
        </p>
        @if($isEnded)
            <span class="text-xs font-medium text-stone-400 bg-stone-100 px-2 py-0.5 rounded-full">Encerrada</span>
        @elseif($poll->ends_at)
            <span class="text-xs text-stone-400">Encerra {{ $poll->ends_at->diffForHumans() }}</span>
        @endif
    </div>

    <div class="space-y-2">
        @foreach($percentages as $option)
            <div>
                @if($this->hasVoted() || $isEnded)
                    {{-- Barra de resultado --}}
                    <div class="relative">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-stone-700 flex items-center gap-1.5">
                                @if($selectedOption === $option['id'])
                                    <x-heroicon-s-check-circle class="w-4 h-4 text-amber-600 shrink-0" />
                                @else
                                    <span class="w-4 h-4 shrink-0"></span>
                                @endif
                                {{ $option['text'] }}
                            </span>
                            <span class="text-xs font-semibold text-stone-600 ml-2 shrink-0">{{ $option['percentage'] }}%</span>
                        </div>
                        <div class="h-2 bg-stone-200 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500 {{ $selectedOption === $option['id'] ? 'bg-amber-500' : 'bg-stone-400' }}"
                                style="width: {{ $option['percentage'] }}%"
                            ></div>
                        </div>
                    </div>
                @else
                    {{-- Botão de voto --}}
                    <button
                        type="button"
                        wire:click="vote({{ $option['id'] }})"
                        wire:loading.attr="disabled"
                        class="w-full text-left px-3 py-2 text-sm text-stone-700 bg-white border border-stone-200 rounded-lg hover:border-amber-400 hover:bg-amber-50 transition-colors duration-150 disabled:opacity-50"
                    >
                        {{ $option['text'] }}
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <p class="mt-3 text-xs text-stone-400">
        {{ $totalVotes }} {{ $totalVotes === 1 ? 'voto' : 'votos' }}
        @if(!$this->hasVoted() && !$isEnded && !auth()->check())
            · <a href="{{ route('login') }}" class="text-amber-600 hover:underline">Entre para votar</a>
        @endif
    </p>
</div>

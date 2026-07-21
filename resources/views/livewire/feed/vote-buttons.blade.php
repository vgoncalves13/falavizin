<div class="flex items-center gap-2">
    <button
        wire:click="vote('helpful')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors duration-150
            {{ $userVote?->value === 'helpful'
                ? 'bg-amber-50 border-amber-300 text-amber-700'
                : 'bg-white border-stone-200 text-stone-600 hover:bg-stone-50' }}"
        aria-label="{{ $userVote?->value === 'helpful' ? 'Remover voto positivo' : 'Votar como útil' }} ({{ $helpfulCount }} votos)"
        aria-pressed="{{ $userVote?->value === 'helpful' ? 'true' : 'false' }}"
    >
        <x-heroicon-o-hand-thumb-up class="w-4 h-4" aria-hidden="true" />
        <span>{{ $helpfulCount }}</span>
    </button>

    <button
        wire:click="vote('not_helpful')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors duration-150
            {{ $userVote?->value === 'not_helpful'
                ? 'bg-red-50 border-red-300 text-red-700'
                : 'bg-white border-stone-200 text-stone-600 hover:bg-stone-50' }}"
        aria-label="{{ $userVote?->value === 'not_helpful' ? 'Remover voto negativo' : 'Votar como não útil' }} ({{ $notHelpfulCount }} votos)"
        aria-pressed="{{ $userVote?->value === 'not_helpful' ? 'true' : 'false' }}"
    >
        <x-heroicon-o-hand-thumb-down class="w-4 h-4" aria-hidden="true" />
        <span>{{ $notHelpfulCount }}</span>
    </button>
</div>

<div class="flex items-center gap-2">
    <button
        wire:click="vote('helpful')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors duration-150
            {{ $userVote?->value === 'helpful'
                ? 'bg-amber-50 border-amber-300 text-amber-700'
                : 'bg-white border-stone-200 text-stone-600 hover:bg-stone-50' }}"
    >
        <x-heroicon-o-hand-thumb-up class="w-4 h-4" />
        <span>{{ $helpfulCount }}</span>
    </button>

    <button
        wire:click="vote('not_helpful')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors duration-150
            {{ $userVote?->value === 'not_helpful'
                ? 'bg-red-50 border-red-300 text-red-700'
                : 'bg-white border-stone-200 text-stone-600 hover:bg-stone-50' }}"
    >
        <x-heroicon-o-hand-thumb-down class="w-4 h-4" />
        <span>{{ $notHelpfulCount }}</span>
    </button>
</div>

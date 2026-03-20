<div class="flex items-center gap-2 flex-wrap">
    <span class="text-xs font-medium text-stone-500">Status do problema:</span>

    <button
        wire:click="setStatus(null)"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors duration-150
            {{ $post->resolution_status === null
                ? 'bg-stone-100 border-stone-300 text-stone-700'
                : 'bg-white border-stone-200 text-stone-500 hover:bg-stone-50' }}"
    >
        <x-heroicon-o-clock class="w-3.5 h-3.5" />
        Aberto
    </button>

    <button
        wire:click="setStatus('em_andamento')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors duration-150
            {{ $post->resolution_status?->value === 'em_andamento'
                ? 'bg-blue-50 border-blue-300 text-blue-700'
                : 'bg-white border-stone-200 text-stone-500 hover:bg-stone-50' }}"
    >
        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
        Em andamento
    </button>

    <button
        wire:click="setStatus('resolvido')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors duration-150
            {{ $post->resolution_status?->value === 'resolvido'
                ? 'bg-green-50 border-green-300 text-green-700'
                : 'bg-white border-stone-200 text-stone-500 hover:bg-stone-50' }}"
    >
        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
        Resolvido
    </button>
</div>

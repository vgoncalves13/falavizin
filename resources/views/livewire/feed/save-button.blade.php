<div>
    <button
        wire:click="toggle"
        type="button"
        class="inline-flex items-center gap-1.5 text-xs transition-colors {{ $saved ? 'text-amber-600' : 'text-stone-400 hover:text-amber-600' }}"
        title="{{ $saved ? 'Remover dos salvos' : 'Salvar post' }}"
    >
        @if($saved)
            <x-heroicon-s-bookmark class="w-4 h-4" />
        @else
            <x-heroicon-o-bookmark class="w-4 h-4" />
        @endif
        @if($savesCount > 0)
            <span>{{ $savesCount }}</span>
        @endif
    </button>
</div>

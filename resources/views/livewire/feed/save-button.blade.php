<div>
    <button
        wire:click="toggle"
        type="button"
        class="inline-flex items-center gap-1.5 text-xs transition-colors {{ $saved ? 'text-amber-600' : 'text-stone-400 hover:text-amber-600' }}"
        aria-label="{{ $saved ? 'Remover dos salvos' : 'Salvar post' }}"
        aria-pressed="{{ $saved ? 'true' : 'false' }}"
    >
        @if($saved)
            <x-heroicon-s-bookmark class="w-4 h-4" aria-hidden="true" />
        @else
            <x-heroicon-o-bookmark class="w-4 h-4" aria-hidden="true" />
        @endif
        @if($savesCount > 0)
            <span>{{ $savesCount }}</span>
        @endif
    </button>
</div>

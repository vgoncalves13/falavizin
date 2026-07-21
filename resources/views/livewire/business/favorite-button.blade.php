<button
    wire:click="toggle"
    wire:loading.attr="disabled"
    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors duration-150
        {{ $isFavorited
            ? 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100'
            : 'bg-white border-stone-200 text-stone-500 hover:bg-stone-50 hover:text-red-500 hover:border-red-200' }}"
    aria-label="{{ $isFavorited ? 'Remover dos favoritos' : 'Salvar nos favoritos' }}"
    aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
>
    @if($isFavorited)
        <x-heroicon-s-heart class="w-4 h-4" aria-hidden="true" />
        Favoritado
    @else
        <x-heroicon-o-heart class="w-4 h-4" aria-hidden="true" />
        Favoritar
    @endif
</button>

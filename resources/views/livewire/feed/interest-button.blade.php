<div class="inline-flex items-center gap-1.5">
    @auth
        @if(auth()->id() !== $post->user_id)
            <button
                wire:click="toggle"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-150 {{ $isInterested ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200' }}"
            >
                @if($isInterested)
                    <x-heroicon-s-hand-raised class="w-4 h-4" />
                    <span>Interessado</span>
                @else
                    <x-heroicon-o-hand-raised class="w-4 h-4" />
                    <span>Tenho interesse</span>
                @endif
            </button>
        @endif
    @endauth

    @if($interestCount > 0)
        <span class="inline-flex items-center gap-1 text-xs text-stone-500">
            <x-heroicon-o-hand-raised class="w-3.5 h-3.5" />
            {{ $interestCount }} {{ $interestCount === 1 ? 'interessado' : 'interessados' }}
        </span>
    @endif
</div>

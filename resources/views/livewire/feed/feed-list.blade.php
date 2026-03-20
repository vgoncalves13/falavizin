<div>
    {{-- Filtro por bairro --}}
    @auth
        @if(auth()->user()->neighborhood)
            <div class="mb-4">
                <button
                    wire:click="toggleNeighborhood"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-150 {{ $neighborhoodOnly ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50' }}"
                >
                    <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                    {{ auth()->user()->neighborhood }}
                </button>
            </div>
        @endif
    @endauth

    {{-- Filtro por categoria --}}
    <div class="flex gap-2 flex-wrap mb-6">
        <button
            wire:click="setCategory(null)"
            class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors duration-150 {{ $categoryId === null ? 'bg-amber-600 text-white' : 'bg-white text-stone-600 border border-stone-200 hover:bg-stone-50' }}"
        >
            Todos
        </button>
        @foreach($categories as $category)
            <button
                wire:click="setCategory({{ $category->id }})"
                class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors duration-150 {{ $categoryId === $category->id ? 'bg-amber-600 text-white' : 'bg-white text-stone-600 border border-stone-200 hover:bg-stone-50' }}"
            >
                {{ $category->name }}
            </button>
        @endforeach
    </div>

    {{-- Lista de posts --}}
    <div class="space-y-3">
        @forelse($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                <x-heroicon-o-newspaper class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                <p class="text-stone-500">Nenhum post encontrado.</p>
                @auth
                    <a href="{{ route('feed.create') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-amber-600 hover:text-amber-700">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Criar o primeiro post
                    </a>
                @endauth
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    @if($posts->hasPages())
        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    @endif
</div>

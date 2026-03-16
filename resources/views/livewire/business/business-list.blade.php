<div>
    {{-- Barra de busca + filtro --}}
    <div class="mb-6 space-y-4">
        <div class="relative">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-stone-400" />
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por nome, bairro ou descrição..."
                class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            />
        </div>

        <div class="flex gap-2 flex-wrap">
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
    </div>

    {{-- Grid de negócios --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($businesses as $business)
            <x-business-card :business="$business" />
        @empty
            <div class="col-span-full bg-white rounded-xl border border-stone-200 p-10 text-center">
                <x-heroicon-o-building-storefront class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                <p class="text-stone-500 mb-1">Nenhum serviço encontrado.</p>
                @if($search)
                    <p class="text-sm text-stone-400">Tente buscar por outro termo.</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    @if($businesses->hasPages())
        <div class="mt-8">
            {{ $businesses->links() }}
        </div>
    @endif
</div>

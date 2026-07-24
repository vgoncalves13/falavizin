<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Bairros</h1>
        @if(! $showForm)
            <button wire:click="create"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                <x-heroicon-o-plus class="w-4 h-4" />
                Novo bairro
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($showForm)
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-stone-900 mb-4" style="font-family: var(--font-display)">
                {{ $editingId ? 'Editar bairro' : 'Novo bairro' }}
            </h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Nome</label>
                        <input type="text" wire:model="name" placeholder="Ex: Copacabana"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Slug</label>
                        <input type="text" wire:model="slug" placeholder="Deixe vazio para gerar automaticamente"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('slug')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Cidade</label>
                        <input type="text" wire:model="city" placeholder="Ex: Rio de Janeiro"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('city')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">City Slug</label>
                        <input type="text" wire:model="citySlug" placeholder="Deixe vazio para gerar"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('city_slug')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">UF</label>
                        <input type="text" wire:model="stateCode" placeholder="RJ" maxlength="2"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('state_code')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Latitude</label>
                        <input type="number" step="any" wire:model="latitude" placeholder="-22.9711"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('latitude')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Longitude</label>
                        <input type="number" step="any" wire:model="longitude" placeholder="-43.1823"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('longitude')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Ordenação</label>
                        <input type="number" wire:model="sortOrder" min="0"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('sort_order')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="isActive" id="is-active"
                           class="rounded border-stone-300 text-amber-600 focus:ring-amber-500" />
                    <label for="is-active" class="text-sm font-medium text-stone-700">Ativo</label>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 disabled:opacity-60">
                    <span wire:loading wire:target="save">
                        <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                    </span>
                    <x-heroicon-o-check class="w-4 h-4" wire:loading.remove wire:target="save" />
                    {{ $editingId ? 'Atualizar' : 'Criar' }}
                </button>
                <button wire:click="cancel"
                        class="px-5 py-2 text-stone-600 hover:text-stone-900 text-sm font-medium rounded-lg hover:bg-stone-100 transition-colors duration-150">
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-stone-200 divide-y divide-stone-100">
        @forelse($neighborhoods as $neighborhood)
            <div class="flex items-center justify-between p-4 gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-stone-900 truncate">{{ $neighborhood->name }}</h3>
                        @if(! $neighborhood->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-stone-100 text-stone-500 rounded-full">Inativo</span>
                        @endif
                    </div>
                    <p class="text-xs text-stone-500 mt-0.5">
                        {{ $neighborhood->city }} - {{ $neighborhood->state_code }} &middot; {{ $neighborhood->slug }}
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button wire:click="$dispatch('edit', { id: {{ $neighborhood->id }} })"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors duration-150">
                        <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                        Editar
                    </button>
                    <button wire:click="toggleActive({{ $neighborhood->id }})"
                            wire:loading.attr="disabled"
                            wire:target="toggleActive"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors duration-150 {{ $neighborhood->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-green-700 hover:bg-green-50' }}">
                        @if($neighborhood->is_active)
                            <x-heroicon-o-eye-slash class="w-3.5 h-3.5" />
                            Desativar
                        @else
                            <x-heroicon-o-eye class="w-3.5 h-3.5" />
                            Ativar
                        @endif
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-heroicon-o-map-pin class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                <p class="text-sm text-stone-500">Nenhum bairro cadastrado.</p>
            </div>
        @endforelse
    </div>
</div>

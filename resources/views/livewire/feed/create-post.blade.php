<div>
    <form wire:submit="save" class="space-y-5">
        {{-- Categoria --}}
        <div>
            <label for="category" class="block text-sm font-medium text-stone-700 mb-1.5">
                Categoria <span class="text-red-500">*</span>
            </label>
            <select
                id="category"
                wire:model="categoryId"
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            >
                <option value="">Selecione uma categoria...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Título --}}
        <div>
            <label for="title" class="block text-sm font-medium text-stone-700 mb-1.5">
                Título <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="title"
                wire:model="title"
                placeholder="Do que se trata?"
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            />
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Conteúdo --}}
        <div>
            <label for="body" class="block text-sm font-medium text-stone-700 mb-1.5">
                Conteúdo <span class="text-red-500">*</span>
            </label>
            <textarea
                id="body"
                wire:model="body"
                rows="6"
                placeholder="Conte mais detalhes..."
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            ></textarea>
            @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Localização --}}
        <div>
            <label for="location" class="block text-sm font-medium text-stone-700 mb-1.5">
                Localização <span class="text-stone-400 font-normal">(opcional)</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <x-heroicon-o-map-pin class="w-4 h-4 text-stone-400" />
                </div>
                <input
                    type="text"
                    id="location"
                    wire:model="location"
                    placeholder="Ex: Rua das Flores, próximo à praça"
                    class="w-full pl-9 rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                />
            </div>
        </div>

        {{-- Botão --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('feed.index') }}" class="text-sm text-stone-500 hover:text-stone-700">
                Cancelar
            </a>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors duration-150"
            >
                <span wire:loading.remove>Publicar</span>
                <span wire:loading>Publicando...</span>
            </button>
        </div>
    </form>
</div>

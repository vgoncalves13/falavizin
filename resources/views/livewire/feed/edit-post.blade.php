<div>
    <form wire:submit="save" class="space-y-5">
        {{-- Categoria --}}
        <div x-data="{ categorySlug: '{{ $post->category->slug }}' }">
            <label for="category" class="block text-sm font-medium text-stone-700 mb-1.5">
                Categoria <span class="text-red-500">*</span>
            </label>
            <select
                id="category"
                wire:model="categoryId"
                x-on:change="categorySlug = $event.target.options[$event.target.selectedIndex].dataset.slug"
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            >
                <option value="">Selecione uma categoria...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" data-slug="{{ $category->slug }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            {{-- Campos de evento --}}
            <div x-show="categorySlug === 'evento'" x-transition class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg space-y-3">
                <p class="text-xs font-medium text-amber-700 flex items-center gap-1.5">
                    <x-heroicon-o-calendar-days class="w-4 h-4" />
                    Datas do Evento
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-stone-600 mb-1">Início</label>
                        <input type="datetime-local" wire:model="eventStartsAt"
                               class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500" />
                        @error('eventStartsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-600 mb-1">Término</label>
                        <input type="datetime-local" wire:model="eventEndsAt"
                               class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500" />
                        @error('eventEndsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
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
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 resize-none"
            ></textarea>
            @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Localização --}}
        <div>
            <label for="location" class="block text-sm font-medium text-stone-700 mb-1.5">Localização</label>
            <input
                type="text"
                id="location"
                wire:model="location"
                placeholder="Ex: Rua das Flores, esquina com Av. Central"
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            />
            @error('location') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ $post->canonicalUrl() }}"
               class="text-sm text-stone-500 hover:text-stone-700 transition-colors">
                Cancelar
            </a>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors"
            >
                <span wire:loading.remove wire:target="save">Salvar alterações</span>
                <span wire:loading wire:target="save">Salvando...</span>
            </button>
        </div>
    </form>
</div>

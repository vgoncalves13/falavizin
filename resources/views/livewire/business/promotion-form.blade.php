<div
    x-data="{ open: {{ $editingId ? 'true' : 'false' }} }"
    @edit-promotion.window="open = true"
    @promotion-saved.window="open = false"
>
    <button
        x-show="!open"
        @click="open = true"
        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150"
    >
        <x-heroicon-o-plus class="w-4 h-4" />
        Nova promoção
    </button>

    <div x-show="open" x-collapse class="mt-4">
        <div class="bg-stone-50 rounded-xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-900 mb-4">
                {{ $editingId ? 'Editar promoção' : 'Adicionar promoção' }}
            </h3>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label for="promo-title" class="block text-sm font-medium text-stone-700 mb-1">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="promo-title"
                        wire:model="title"
                        placeholder="Ex: 20% off na primeira compra"
                        class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                    />
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="promo-desc" class="block text-sm font-medium text-stone-700 mb-1">
                        Descrição <span class="text-stone-400 font-normal">(opcional)</span>
                    </label>
                    <textarea
                        id="promo-desc"
                        wire:model="description"
                        rows="3"
                        placeholder="Mais detalhes sobre a promoção..."
                        class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="starts-at" class="block text-sm font-medium text-stone-700 mb-1">
                            Início <span class="text-stone-400 font-normal">(opcional)</span>
                        </label>
                        <input
                            type="date"
                            id="starts-at"
                            wire:model="startsAt"
                            class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                        />
                        @error('startsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ends-at" class="block text-sm font-medium text-stone-700 mb-1">
                            Término <span class="text-stone-400 font-normal">(opcional)</span>
                        </label>
                        <input
                            type="date"
                            id="ends-at"
                            wire:model="endsAt"
                            class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                        />
                        @error('endsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button
                        type="button"
                        @click="open = false"
                        wire:click="cancelEdit"
                        class="text-sm text-stone-500 hover:text-stone-700"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors duration-150"
                    >
                        <span wire:loading.remove>{{ $editingId ? 'Salvar alterações' : 'Publicar promoção' }}</span>
                        <span wire:loading>Salvando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div>
    <form wire:submit="save" class="space-y-5">
        {{-- Bairro --}}
        <div class="rounded-xl border border-[#FD5C3E]/25 bg-[#FD5C3E]/5 px-4 py-3">
            <p class="text-sm font-semibold text-stone-900">
                Publicando em {{ $neighborhood->name }}
            </p>
        </div>

        {{-- Categoria --}}
        <div x-data="{ categorySlug: '' }">
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

            {{-- Categoria de serviço (visível apenas para categoria "pedido") --}}
            <div x-show="categorySlug === 'pedido'" x-transition class="mt-4">
                <label for="serviceCategory" class="block text-sm font-medium text-stone-700 mb-1.5">
                    Tipo de serviço procurado <span class="text-stone-400 font-normal">(opcional)</span>
                </label>
                <select
                    id="serviceCategory"
                    wire:model="serviceCategoryId"
                    class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                >
                    <option value="">Qualquer categoria...</option>
                    @foreach($serviceCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-stone-400">Ajuda comerciantes da área a encontrarem seu pedido</p>
            </div>

            {{-- Campos de evento (visível apenas para categoria "evento") --}}
            <div x-show="categorySlug === 'evento'" x-transition class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg space-y-3">
                <p class="text-xs font-medium text-amber-700 flex items-center gap-1.5">
                    <x-heroicon-o-calendar-days class="w-4 h-4" />
                    Datas do Evento
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-stone-600 mb-1">Início</label>
                        <input
                            type="datetime-local"
                            wire:model="eventStartsAt"
                            class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                        />
                        @error('eventStartsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-600 mb-1">Término</label>
                        <input
                            type="datetime-local"
                            wire:model="eventEndsAt"
                            class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                        />
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

        {{-- Imagem --}}
        <div
            x-data="{ preview: null }"
            x-on:livewire-upload-start="$el.querySelector('label').classList.add('opacity-50')"
            x-on:livewire-upload-finish="$el.querySelector('label').classList.remove('opacity-50')"
        >
            <label class="block text-sm font-medium text-stone-700 mb-1.5">
                Imagem <span class="text-stone-400 font-normal">(opcional)</span>
            </label>
            <label
                class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-stone-300 rounded-lg cursor-pointer bg-stone-50 hover:bg-stone-100 transition-colors duration-150"
                x-show="!preview"
            >
                <x-heroicon-o-photo class="w-8 h-8 text-stone-400 mb-2" />
                <span class="text-xs text-stone-500">JPG, PNG ou WebP · máx. 2MB</span>
                <input
                    type="file"
                    wire:model="image"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    x-on:change="
                        const file = $event.target.files[0];
                        if (file) { const reader = new FileReader(); reader.onload = e => preview = e.target.result; reader.readAsDataURL(file); }
                    "
                />
            </label>
            <div x-show="preview" class="relative rounded-lg overflow-hidden">
                <img :src="preview" class="w-full h-48 object-cover rounded-lg" />
                <button
                    type="button"
                    x-on:click="preview = null; $wire.set('image', null)"
                    class="absolute top-2 right-2 bg-black/50 hover:bg-black/70 text-white rounded-full p-1 transition-colors"
                >
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
            @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Localização --}}
        <div
            x-data="{
                loading: false,
                getLocation() {
                    if (!navigator.geolocation) return;
                    this.loading = true;
                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            try {
                                const res = await fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + pos.coords.latitude + '&lon=' + pos.coords.longitude + '&addressdetails=1');
                                const data = await res.json();
                                const addr = data.address;
                                const label = [addr.road, addr.suburb || addr.neighbourhood, addr.city || addr.town].filter(Boolean).join(', ');
                                $wire.set('location', label);
                            } catch(e) {}
                            this.loading = false;
                        },
                        () => { this.loading = false; }
                    );
                }
            }"
        >
            <label for="location" class="block text-sm font-medium text-stone-700 mb-1.5">
                Localização <span class="text-stone-400 font-normal">(opcional)</span>
            </label>
            <div class="flex gap-2">
                <div class="relative flex-1">
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
                <button
                    type="button"
                    x-on:click="getLocation"
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium bg-stone-100 hover:bg-stone-200 text-stone-600 rounded-lg border border-stone-300 transition-colors disabled:opacity-50"
                    title="Usar minha localização"
                >
                    <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                    <span x-show="!loading">Usar localização</span>
                    <span x-show="loading">Detectando...</span>
                </button>
            </div>
        </div>

        {{-- Enquete --}}
        <div
            x-data="{ open: @entangle('hasPoll') }"
        >
            <button
                type="button"
                x-on:click="open = !open; $wire.set('hasPoll', open)"
                class="inline-flex items-center gap-2 text-sm font-medium text-amber-700 hover:text-amber-800 transition-colors"
            >
                <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                <span x-text="open ? 'Remover enquete' : 'Adicionar enquete'"></span>
            </button>

            <div x-show="open" x-transition class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-lg space-y-3">
                <div>
                    <label class="block text-xs font-medium text-stone-600 mb-1">Pergunta da enquete</label>
                    <input
                        type="text"
                        wire:model="pollQuestion"
                        placeholder="O que você quer perguntar?"
                        class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                    />
                    @error('pollQuestion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-medium text-stone-600">Opções</label>
                    @foreach($pollOptions as $index => $option)
                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model="pollOptions.{{ $index }}"
                                placeholder="Opção {{ $index + 1 }}"
                                class="flex-1 rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                            />
                            @if(count($pollOptions) > 2)
                                <button
                                    type="button"
                                    wire:click="removePollOption({{ $index }})"
                                    class="p-2 text-stone-400 hover:text-red-500 transition-colors"
                                >
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </button>
                            @endif
                        </div>
                        @error('pollOptions.' . $index) <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    @endforeach

                    @if(count($pollOptions) < 5)
                        <button
                            type="button"
                            wire:click="addPollOption"
                            class="inline-flex items-center gap-1 text-xs text-amber-700 hover:text-amber-800 transition-colors"
                        >
                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                            Adicionar opção
                        </button>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-stone-600 mb-1">Encerrar em <span class="font-normal text-stone-400">(opcional)</span></label>
                    <input
                        type="datetime-local"
                        wire:model="pollEndsAt"
                        class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                    />
                </div>
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

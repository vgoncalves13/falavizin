<div>
    @if($photos->isEmpty() && ! $canManage)
        {{-- Nada a mostrar para visitantes sem fotos --}}
    @else
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-stone-900">Fotos</h2>
                <span class="text-xs text-stone-400">{{ $photos->count() }} {{ $photos->count() === 1 ? 'foto' : 'fotos' }}</span>
            </div>

            {{-- Grade de fotos + Lightbox --}}
            @if($photos->isNotEmpty())
                @php $photoUrls = $photos->map(fn($p) => Storage::url($p->path))->values()->toJson(JSON_UNESCAPED_SLASHES); @endphp

                <div
                    x-data="{ open: false, current: 0, photos: [], show(i) { this.current = i; this.open = true; }, prev() { this.current = (this.current - 1 + this.photos.length) % this.photos.length; }, next() { this.current = (this.current + 1) % this.photos.length; } }"
                    x-init="photos = JSON.parse($el.dataset.photos)"
                    data-photos="{{ $photoUrls }}"
                    @keydown.escape.window="open = false"
                    @keydown.arrow-left.window="open && prev()"
                    @keydown.arrow-right.window="open && next()"
                >
                    {{-- Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
                        @foreach($photos as $index => $photo)
                            <div class="relative group rounded-lg overflow-hidden bg-stone-100 aspect-square">
                                <button
                                    type="button"
                                    @click="show({{ $index }})"
                                    class="w-full h-full block"
                                >
                                    <img
                                        src="{{ Storage::url($photo->path) }}"
                                        alt="Foto do negócio"
                                        class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    />
                                </button>

                                @if($photo->is_cover)
                                    <span class="absolute top-2 left-2 inline-flex items-center gap-1 px-2 py-0.5 bg-amber-500 text-white text-xs font-medium rounded-full shadow pointer-events-none">
                                        <x-heroicon-s-star class="w-3 h-3" />
                                        Capa
                                    </span>
                                @endif

                                @if($canManage)
                                    {{-- Overlay escuro no hover (visual only, sem captura de cliques) --}}
                                    <div class="absolute inset-0 bg-stone-900/0 group-hover:bg-stone-900/40 transition-all duration-200 pointer-events-none"></div>
                                    {{-- Botões posicionados individualmente --}}
                                    <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        @unless($photo->is_cover)
                                            <button
                                                wire:click.stop="setCover({{ $photo->id }})"
                                                wire:loading.attr="disabled"
                                                title="Definir como capa"
                                                class="p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow"
                                            >
                                                <x-heroicon-o-star class="w-4 h-4" />
                                            </button>
                                        @endunless
                                        <button
                                            wire:click.stop="delete({{ $photo->id }})"
                                            wire:confirm="Remover esta foto?"
                                            wire:loading.attr="disabled"
                                            title="Remover foto"
                                            class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors shadow"
                                        >
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Lightbox --}}
                    <div
                        x-show="open"
                        x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/90"
                        @click.self="open = false"
                        style="display: none;"
                    >
                        {{-- Fechar --}}
                        <button
                            type="button"
                            @click="open = false"
                            class="absolute top-4 right-4 p-2 text-white/70 hover:text-white transition-colors"
                        >
                            <x-heroicon-o-x-mark class="w-7 h-7" />
                        </button>

                        {{-- Contador --}}
                        <span class="absolute top-4 left-1/2 -translate-x-1/2 text-sm text-white/60">
                            <span x-text="current + 1"></span> / {{ $photos->count() }}
                        </span>

                        {{-- Anterior --}}
                        @if($photos->count() > 1)
                            <button
                                type="button"
                                @click.stop="prev()"
                                class="absolute left-3 sm:left-6 p-2 text-white/70 hover:text-white transition-colors"
                            >
                                <x-heroicon-o-chevron-left class="w-8 h-8" />
                            </button>
                        @endif

                        {{-- Imagem principal --}}
                        <div class="max-w-5xl max-h-screen w-full px-16 sm:px-24 flex items-center justify-center">
                            <img
                                :src="photos[current]"
                                :key="photos[current]"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                alt="Foto ampliada"
                                class="max-h-[85vh] max-w-full object-contain rounded-lg shadow-2xl"
                            />
                        </div>

                        {{-- Próxima --}}
                        @if($photos->count() > 1)
                            <button
                                type="button"
                                @click.stop="next()"
                                class="absolute right-3 sm:right-6 p-2 text-white/70 hover:text-white transition-colors"
                            >
                                <x-heroicon-o-chevron-right class="w-8 h-8" />
                            </button>
                        @endif

                        {{-- Miniaturas --}}
                        @if($photos->count() > 1)
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 max-w-xs sm:max-w-md overflow-x-auto px-2">
                                @foreach($photos as $index => $photo)
                                    <button
                                        type="button"
                                        @click.stop="current = {{ $index }}"
                                        :class="current === {{ $index }} ? 'ring-2 ring-amber-400 opacity-100' : 'opacity-50 hover:opacity-80'"
                                        class="w-12 h-12 shrink-0 rounded-md overflow-hidden transition-all duration-150"
                                    >
                                        <img src="{{ Storage::url($photo->path) }}" class="w-full h-full object-cover" alt="" />
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Upload (dono) --}}
            @if($canManage)
                <div
                    x-data="{
                        previewUrls: [],
                        dragover: false,
                        handleFiles(files) {
                            this.previewUrls = [];
                            Array.from(files).forEach(f => {
                                const reader = new FileReader();
                                reader.onload = e => this.previewUrls.push(e.target.result);
                                reader.readAsDataURL(f);
                            });
                        }
                    }"
                >
                    <label
                        for="new-photos-input"
                        @dragover.prevent="dragover = true"
                        @dragleave.prevent="dragover = false"
                        @drop.prevent="dragover = false; handleFiles($event.dataTransfer.files)"
                        :class="dragover ? 'border-amber-400 bg-amber-50' : 'border-stone-200 bg-stone-50 hover:bg-stone-100'"
                        class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-8 cursor-pointer transition-colors"
                    >
                        <x-heroicon-o-camera class="w-8 h-8 text-stone-400" />
                        <span class="text-sm font-medium text-stone-600">Adicionar fotos</span>
                        <span class="text-xs text-stone-400">Arraste ou clique · JPG, PNG até 5MB cada</span>

                        <input
                            id="new-photos-input"
                            type="file"
                            multiple
                            accept="image/*"
                            class="hidden"
                            x-on:change="handleFiles($event.target.files)"
                            wire:model="newPhotos"
                        />
                    </label>

                    @error('newPhotos.*')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <template x-if="previewUrls.length > 0">
                        <div class="mt-4">
                            <div class="flex gap-2 flex-wrap mb-3">
                                <template x-for="url in previewUrls" :key="url">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-stone-100 shrink-0">
                                        <img :src="url" class="w-full h-full object-cover" />
                                    </div>
                                </template>
                            </div>
                            <button
                                wire:click="savePhotos"
                                wire:loading.attr="disabled"
                                x-on:click="previewUrls = []"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                <span wire:loading.remove wire:target="savePhotos">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 inline mr-1" />
                                    Salvar fotos
                                </span>
                                <span wire:loading wire:target="savePhotos">Enviando...</span>
                            </button>
                        </div>
                    </template>
                </div>
            @endif
        </div>
    @endif
</div>

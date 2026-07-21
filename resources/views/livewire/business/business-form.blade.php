<div>
    <form wire:submit="save" class="space-y-5">
        {{-- Nome --}}
        <div>
            <label for="name" class="block text-sm font-medium text-stone-700 mb-1.5">
                Nome do negócio <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="name"
                wire:model="name"
                placeholder="Ex: Padaria do João"
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            />
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Categoria --}}
        <div>
            <label for="categoryId" class="block text-sm font-medium text-stone-700 mb-1.5">
                Categoria <span class="text-red-500">*</span>
            </label>
            <select
                id="categoryId"
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

        {{-- Bairro --}}
        <div>
            <label for="neighborhood" class="block text-sm font-medium text-stone-700 mb-1.5">
                Bairro <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="neighborhood"
                wire:model="neighborhood"
                placeholder="Ex: Engenho da Rainha"
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            />
            @error('neighborhood') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- WhatsApp --}}
        <div>
            <label for="whatsapp" class="block text-sm font-medium text-stone-700 mb-1.5">
                WhatsApp <span class="text-stone-400 font-normal">(opcional)</span>
            </label>
            <input
                type="text"
                id="whatsapp"
                wire:model="whatsapp"
                placeholder="(21) 9 9999-9999"
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            />
            <p class="mt-1 text-xs text-stone-400">Principal canal de contato com clientes</p>
            @error('whatsapp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Descrição --}}
        <div>
            <label for="description" class="block text-sm font-medium text-stone-700 mb-1.5">
                Descrição <span class="text-stone-400 font-normal">(opcional)</span>
            </label>
            <textarea
                id="description"
                wire:model="description"
                rows="3"
                placeholder="Descreva seus serviços, diferenciais..."
                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
            ></textarea>
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Foto de capa --}}
        <div>
            <label for="coverPhoto" class="block text-sm font-medium text-stone-700 mb-1.5">
                Foto de capa <span class="text-stone-400 font-normal">(opcional, máx. 5MB)</span>
            </label>
            <input
                type="file"
                id="coverPhoto"
                wire:model="coverPhoto"
                accept="image/*"
                class="w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100"
            />
            <div wire:loading wire:target="coverPhoto" class="mt-1 text-xs text-stone-400">Carregando imagem...</div>
            @if ($coverPhoto)
                <div class="mt-2">
                    <img src="{{ $coverPhoto->temporaryUrl() }}" class="h-24 w-40 object-cover rounded-lg border border-stone-200" alt="Preview" />
                </div>
            @endif
            @error('coverPhoto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Info (apenas no cadastro) --}}
        @if(! $business?->exists)
            <div class="bg-stone-50 rounded-lg p-4 border border-stone-200">
                <p class="text-xs text-stone-500">
                    <strong>Dica:</strong> Após o cadastro, você pode adicionar mais informações como endereço, telefone, horários e fotos.
                </p>
            </div>
        @endif

        {{-- Campos extras (apenas na edição) --}}
        @if($business?->exists)
            <div class="border-t border-stone-200 pt-5">
                <h3 class="text-sm font-semibold text-stone-700 mb-4">Informações adicionais</h3>

                <div class="space-y-5">
                    {{-- Telefones --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-medium text-stone-700">
                                Telefone(s) <span class="text-stone-400 font-normal">(opcional)</span>
                            </label>
                            @if(count($phones) < 5)
                                <button type="button" wire:click="addPhone"
                                        class="text-xs text-amber-600 hover:text-amber-700 font-medium flex items-center gap-0.5">
                                    <x-heroicon-o-plus class="w-3.5 h-3.5" /> Adicionar
                                </button>
                            @endif
                        </div>
                        <div class="space-y-2">
                            @foreach($phones as $i => $phone)
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        wire:model="phones.{{ $i }}"
                                        placeholder="(21) 9999-9999"
                                        class="flex-1 rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                                    />
                                    @if(count($phones) > 1)
                                        <button type="button" wire:click="removePhone({{ $i }})"
                                                class="text-stone-400 hover:text-red-500 transition-colors shrink-0">
                                            <x-heroicon-o-x-mark class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                                @error("phones.{$i}") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            @endforeach
                        </div>
                    </div>

                    {{-- Endereço --}}
                    <div>
                        <label for="address" class="block text-sm font-medium text-stone-700 mb-1.5">
                            Endereço <span class="text-stone-400 font-normal">(opcional)</span>
                        </label>
                        <input
                            type="text"
                            id="address"
                            wire:model="address"
                            placeholder="Rua, número, complemento"
                            class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Cidade --}}
                        <div>
                            <label for="city" class="block text-sm font-medium text-stone-700 mb-1.5">
                                Cidade <span class="text-stone-400 font-normal">(opcional)</span>
                            </label>
                            <input
                                type="text"
                                id="city"
                                wire:model="city"
                                placeholder="Ex: Rio de Janeiro"
                                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        {{-- Website --}}
                        <div>
                            <label for="website" class="block text-sm font-medium text-stone-700 mb-1.5">
                                Website <span class="text-stone-400 font-normal">(opcional)</span>
                            </label>
                            <input
                                type="url"
                                id="website"
                                wire:model="website"
                                placeholder="https://..."
                                class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                            />
                            @error('website') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Horários de funcionamento --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <x-heroicon-o-clock class="w-4 h-4 text-stone-400" />
                            <label class="text-sm font-medium text-stone-700">
                                Horários de funcionamento <span class="text-stone-400 font-normal">(opcional)</span>
                            </label>
                        </div>
                        <div class="rounded-lg border border-stone-200 overflow-hidden divide-y divide-stone-100">
                            @foreach($openingHours as $i => $hours)
                                <div class="flex items-center gap-3 px-4 py-2.5 bg-white hover:bg-stone-50 transition-colors">
                                    <span class="w-28 text-sm text-stone-600 shrink-0">{{ $hours['day'] }}</span>

                                    <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                                        <input type="checkbox"
                                               wire:model="openingHours.{{ $i }}.closed"
                                               class="rounded border-stone-300 text-amber-600 focus:ring-amber-500" />
                                        <span class="text-xs text-stone-500">Fechado</span>
                                    </label>

                                    @if(! $hours['closed'])
                                        <div class="flex items-center gap-2 ml-auto">
                                            <input type="time"
                                                   wire:model="openingHours.{{ $i }}.open"
                                                   class="rounded border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 py-1 px-2" />
                                            <span class="text-stone-400 text-sm">–</span>
                                            <input type="time"
                                                   wire:model="openingHours.{{ $i }}.close"
                                                   class="rounded border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 py-1 px-2" />
                                        </div>
                                    @else
                                        <span class="ml-auto text-xs text-stone-400 italic">Fechado</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ $business?->exists ? route('businesses.show', $business) : route('businesses.index') }}" class="text-sm text-stone-500 hover:text-stone-700">
                Cancelar
            </a>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors duration-150"
            >
                <span wire:loading.remove>{{ $business?->exists ? 'Salvar alterações' : 'Cadastrar negócio' }}</span>
                <span wire:loading>Salvando...</span>
            </button>
        </div>
    </form>
</div>

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

        {{-- Info --}}
        <div class="bg-stone-50 rounded-lg p-4 border border-stone-200">
            <p class="text-xs text-stone-500">
                <strong>Dica:</strong> Após o cadastro, você pode adicionar mais informações como endereço, telefone, horários e fotos pelo painel "Meu Negócio".
            </p>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('businesses.index') }}" class="text-sm text-stone-500 hover:text-stone-700">
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

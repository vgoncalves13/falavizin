@props([
    'action',
    'triggerClass' => '',
    'triggerLabel' => 'Reportar',
])

<div
    x-data="{ open: false, reason: '' }"
    @keydown.escape.window="open = false"
>
    {{-- Trigger button --}}
    <button
        type="button"
        @click="open = true"
        {{ $attributes->merge(['class' => $triggerClass]) }}
        aria-haspopup="dialog"
    >
        <x-heroicon-o-flag class="w-4 h-4" aria-hidden="true" />
        {{ $triggerLabel }}
    </button>

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-stone-900/50"
        @click="open = false"
        style="display: none;"
        aria-hidden="true"
    ></div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="report-modal-title"
        x-init="$watch('open', value => { if(value) $nextTick(() => $el.querySelector('select').focus()) })"
    >
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl ring-1 ring-stone-200">
            <div class="flex items-center justify-between border-b border-stone-100 px-6 py-4">
                <div class="flex items-center gap-2 text-stone-900">
                    <x-heroicon-o-flag class="w-5 h-5 text-amber-600" aria-hidden="true" />
                    <h3 id="report-modal-title" class="font-semibold">Reportar conteúdo</h3>
                </div>
                <button type="button" @click="open = false" class="text-stone-400 hover:text-stone-600 transition-colors"
                        aria-label="Fechar modal de report">
                    <x-heroicon-o-x-mark class="w-5 h-5" aria-hidden="true" />
                </button>
            </div>

            <form method="POST" action="{{ $action }}" class="px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label for="reason-select" class="block text-sm font-medium text-stone-700 mb-2">
                        Qual o motivo do report?
                    </label>
                    <select
                        id="reason-select"
                        x-model="reason"
                        class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                    >
                        <option value="">Selecione um motivo...</option>
                        <option value="Conteúdo inapropriado ou ofensivo">Conteúdo inapropriado ou ofensivo</option>
                        <option value="Spam ou publicidade indevida">Spam ou publicidade indevida</option>
                        <option value="Informação falsa ou enganosa">Informação falsa ou enganosa</option>
                        <option value="Assédio ou intimidação">Assédio ou intimidação</option>
                        <option value="Violação de privacidade">Violação de privacidade</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <div x-show="reason === 'Outro'" style="display: none;">
                    <label for="reason-other" class="block text-sm font-medium text-stone-700 mb-2">
                        Descreva o motivo
                    </label>
                    <textarea
                        id="reason-other"
                        name="reason"
                        rows="3"
                        maxlength="255"
                        placeholder="Explique brevemente o problema..."
                        class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none resize-none"
                    ></textarea>
                </div>

                {{-- Hidden input that carries the selected preset reason --}}
                <template x-if="reason !== 'Outro' && reason !== ''">
                    <input type="hidden" name="reason" :value="reason" />
                </template>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-stone-600 hover:bg-stone-100 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="reason === ''"
                        :class="reason === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-700'"
                        class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition-colors"
                    >
                        Enviar report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

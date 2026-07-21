<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-stone-900" style="font-family: var(--font-display)">
            Preferências de notificação
        </h2>
        <span
            x-data="{ show: false }"
            x-on:preferences-saved.window="show = true; setTimeout(() => show = false, 2000)"
            x-show="show"
            x-transition
            class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full"
        >
            Salvo
        </span>
    </div>

    <p class="text-sm text-stone-500 mb-5">
        Escolha quais notificações deseja receber por email.
    </p>

    <div class="space-y-3">
        @foreach($types as $type => $label)
            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-stone-200">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-bell class="w-5 h-5 text-stone-400" />
                    <span class="text-sm font-medium text-stone-700">{{ $label }}</span>
                </div>
                <button
                    wire:click="togglePreference('{{ $type }}')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2
                        {{ ($preferences[$type] ?? true) ? 'bg-amber-600' : 'bg-stone-200' }}"
                    role="switch"
                    aria-checked="{{ ($preferences[$type] ?? true) ? 'true' : 'false' }}"
                    aria-label="{{ $label }}"
                >
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200
                        {{ ($preferences[$type] ?? true) ? 'translate-x-6' : 'translate-x-1' }}"
                    ></span>
                </button>
            </div>
        @endforeach
    </div>

    <p class="mt-4 text-xs text-stone-400">
        Notificações de segurança (reset de senha) são sempre enviadas.
    </p>
</div>

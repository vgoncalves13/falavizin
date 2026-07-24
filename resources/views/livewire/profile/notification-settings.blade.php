<div data-push-settings>
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
        Os avisos dentro do FalaVizin continuam ativos. Você decide quais também chegam por email ou neste dispositivo.
    </p>

    <section class="mb-6 rounded-xl border border-[#063EAE]/20 bg-[#063EAE]/5 p-4 sm:p-5" aria-labelledby="device-notifications-title">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#063EAE] text-white">
                <x-heroicon-o-device-phone-mobile class="h-5 w-5" />
            </span>
            <div class="min-w-0 flex-1">
                <h3 id="device-notifications-title" class="text-sm font-bold text-stone-900">
                    Notificações neste dispositivo
                </h3>
                <p data-push-status class="mt-1 text-sm text-stone-600" aria-live="polite">
                    Verificando compatibilidade...
                </p>
                <p data-push-guidance class="mt-2 hidden text-xs leading-5 text-stone-600">
                    A permissão foi bloqueada. Para ativar, abra as configurações deste site no navegador e permita notificações.
                </p>
                <p data-push-unsupported class="mt-2 hidden text-xs leading-5 text-stone-600">
                    Você ainda pode acompanhar todos os avisos pela central de notificações do FalaVizin.
                </p>
                <p class="mt-2 text-xs leading-5 text-stone-500">
                    Para ativar, selecione ao menos um tipo abaixo. Desativar aqui afeta somente este navegador; os demais dispositivos continuam ativos.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        type="button"
                        data-push-enable
                        @disabled(! $hasSelectedPushTypes)
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#FD5C3E] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#e94c31] disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <x-heroicon-o-bell-alert class="h-4 w-4" />
                        Receber notificações neste dispositivo
                    </button>
                    <button
                        type="button"
                        data-push-disable
                        class="hidden rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition-colors hover:bg-stone-50 disabled:opacity-50"
                    >
                        Desativar neste dispositivo
                    </button>
                    <button
                        type="button"
                        data-pwa-install
                        class="rounded-lg px-3 py-2 text-sm font-semibold text-[#063EAE] transition-colors hover:bg-white/70"
                    >
                        Instalar aplicativo
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="overflow-hidden rounded-xl border border-stone-200">
        <div class="grid grid-cols-[1fr_64px_64px_64px] items-center gap-2 bg-stone-50 px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-stone-500 sm:grid-cols-[1fr_80px_80px_80px] sm:px-4">
            <span class="text-left">Tipo de aviso</span>
            <span>No site</span>
            <span>Email</span>
            <span>Push</span>
        </div>

        @foreach($types as $type => $settings)
            <div wire:key="notification-type-{{ $type }}" class="grid grid-cols-[1fr_64px_64px_64px] items-center gap-2 border-t border-stone-200 px-3 py-3 sm:grid-cols-[1fr_80px_80px_80px] sm:px-4">
                <div class="min-w-0 pr-1">
                    <p class="text-sm font-semibold text-stone-800">{{ $settings['label'] }}</p>
                    <p class="mt-0.5 text-xs leading-4 text-stone-500">{{ $settings['description'] }}</p>
                </div>

                <span class="mx-auto inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-50 text-green-700" title="Sempre ativo no FalaVizin">
                    <x-heroicon-o-check class="h-4 w-4" />
                    <span class="sr-only">Ativo no FalaVizin</span>
                </span>

                @if($settings['email'])
                    <input
                        type="checkbox"
                        wire:click="togglePreference('email', '{{ $type }}')"
                        @checked($preferences[$type] ?? true)
                        class="mx-auto h-5 w-5 rounded border-stone-300 text-[#FD5C3E] focus:ring-[#FD5C3E]"
                        aria-label="Receber {{ $settings['label'] }} por email"
                    >
                @else
                    <span class="text-center text-stone-300" aria-label="Indisponível por email">—</span>
                @endif

                @if($settings['push'])
                    <input
                        type="checkbox"
                        value="{{ $type }}"
                        data-push-type
                        wire:click="togglePreference('push', '{{ $type }}')"
                        @checked($preferences['push'][$type] ?? false)
                        class="mx-auto h-5 w-5 rounded border-stone-300 text-[#063EAE] focus:ring-[#063EAE]"
                        aria-label="Receber {{ $settings['label'] }} por push"
                    >
                @else
                    <span class="text-center text-stone-300" aria-label="Indisponível por push">—</span>
                @endif
            </div>
        @endforeach
    </div>

    <p class="mt-4 text-xs text-stone-400">
        Notificações de segurança (reset de senha) são sempre enviadas.
    </p>
</div>

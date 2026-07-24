<aside
    data-pwa-install-prompt
    class="fixed inset-x-4 bottom-4 z-[70] mx-auto hidden max-w-lg rounded-2xl border border-stone-200 bg-white p-4 shadow-2xl shadow-stone-900/15 sm:inset-x-auto sm:right-6 sm:bottom-6 sm:p-5"
    role="dialog"
    aria-labelledby="pwa-install-title"
    aria-describedby="pwa-install-description"
>
    <div class="flex items-start gap-3">
        <img
            src="/assets/icons/icon-192.png"
            alt=""
            class="h-12 w-12 shrink-0 rounded-xl"
        >
        <div class="min-w-0">
            <h2 id="pwa-install-title" data-pwa-title class="font-display text-base font-bold text-stone-900">
                Leve o FalaVizin com você
            </h2>
            <p id="pwa-install-description" data-pwa-install-description class="mt-1 text-sm leading-5 text-stone-600">
                Instale o FalaVizin para acessar mais rápido e receber novidades da sua vizinhança.
            </p>
            <p data-pwa-ios-instructions class="mt-2 hidden text-sm leading-5 text-stone-600">
                No iPhone ou iPad, toque em <strong>Compartilhar</strong> e depois em
                <strong>Adicionar à Tela de Início</strong>.
            </p>
        </div>
    </div>

    <div class="mt-4 flex justify-end gap-2">
        <button
            type="button"
            data-pwa-dismiss
            class="rounded-lg px-4 py-2 text-sm font-semibold text-stone-600 transition-colors hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-[#FD5C3E]/30"
        >
            Agora não
        </button>
        <button
            type="button"
            data-pwa-confirm
            class="rounded-lg bg-[#FD5C3E] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#e94c31] focus:outline-none focus:ring-2 focus:ring-[#FD5C3E]/30 focus:ring-offset-2"
        >
            Instalar
        </button>
    </div>
</aside>

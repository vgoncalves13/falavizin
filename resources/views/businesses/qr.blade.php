<x-app-layout title="QR Code — {{ $business->name }}">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('businesses.onboarding', $business) }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar à configuração
        </a>

        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-stone-100">
                <h1 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">
                    QR Code de {{ $business->name }}
                </h1>
                <p class="text-sm text-stone-500 mt-1">
                    Imprima e coloque no seu estabelecimento para que os clientes encontrem seu perfil no FalaVizin.
                </p>
            </div>

            <div class="p-6 flex flex-col sm:flex-row items-center gap-6">
                <div class="shrink-0 bg-white rounded-xl border border-stone-200 p-4">
                    <img src="{{ app(\App\Services\BusinessQrCodeService::class)->inlineFor($business) }}"
                         alt="QR Code de {{ $business->name }}"
                         class="w-48 h-48 sm:w-56 sm:h-56 block" />
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <p class="text-sm text-stone-600">
                        <span class="font-semibold text-stone-900">{{ $business->name }}</span><br />
                        {{ $business->neighborhood }}{{ $business->address ? ' · '.$business->address : '' }}
                    </p>
                    <p class="text-xs text-stone-400 mt-1 break-all">{{ $business->canonicalUrl() }}</p>

                    <div class="mt-4 flex flex-col sm:flex-row gap-2 justify-center sm:justify-start">
                        <a href="{{ route('businesses.qr.download', $business) }}"
                           class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-lg transition-colors">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            Baixar PNG
                        </a>
                        <form action="{{ route('businesses.qr.confirm', $business) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-check class="w-4 h-4" />
                                Confirmar instalação
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

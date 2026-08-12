<x-app-layout title="Placa FalaVizin — {{ $business->name }}">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('businesses.onboarding', $business) }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar à configuração
        </a>

        <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
            <div>
                <h1 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">
                    Placa FalaVizin de {{ $business->name }}
                </h1>
                <p class="text-sm text-stone-500 mt-1">
                    Imprima em A6 e coloque no seu estabelecimento para que os clientes encontrem e avaliem seu perfil.
                </p>
            </div>
        </div>

        <div class="grid lg:grid-cols-[minmax(0,1fr)_240px] gap-6 items-start">
            {{-- Prévia do poster --}}
            <div class="bg-white rounded-xl border border-stone-200 p-4 sm:p-6 flex justify-center">
                <img src="{{ app(\App\Services\BusinessPosterService::class)->inlineFor($business) }}"
                     alt="Placa FalaVizin de {{ $business->name }}"
                     class="w-full max-w-sm block rounded-lg border border-stone-200 shadow-lg" />
            </div>

            {{-- Ações --}}
            <div class="space-y-3 lg:sticky lg:top-6">
                <a href="{{ route('businesses.qr.download', $business) }}"
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Baixar imagem (PNG)
                </a>
                <a href="{{ route('businesses.qr.download-pdf', $business) }}"
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-semibold rounded-lg transition-colors">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                    Baixar PDF (A6)
                </a>

                <div class="bg-stone-50 border border-stone-200 rounded-lg p-4 text-xs text-stone-500 space-y-2">
                    <p class="flex items-center gap-1.5">
                        <x-heroicon-o-printer class="w-4 h-4 text-stone-400" />
                        Impressão recomendada em A6 (105×148mm), sem margens.
                    </p>
                    @if($business->is_founder)
                        <p class="flex items-center gap-1.5">
                            <x-heroicon-s-star class="w-4 h-4 text-amber-500" />
                            Inclui o selo Comércio Fundador.
                        </p>
                    @endif
                </div>

                <form action="{{ route('businesses.qr.confirm', $business) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Confirmar instalação
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

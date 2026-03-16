<x-app-layout>
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl border border-stone-200 p-6 text-center">
            <x-heroicon-o-building-storefront class="w-12 h-12 text-amber-600 mx-auto mb-4" />
            <h1 class="text-xl font-bold text-stone-900 mb-2" style="font-family: var(--font-display)">
                Reivindicar {{ $business->name }}
            </h1>
            <p class="text-stone-500 mb-6">Confirme que você é o responsável por este negócio.</p>

            {{-- Ação de reivindicação virá na Semana 3 --}}
            <p class="text-sm text-stone-400">Funcionalidade em desenvolvimento.</p>
        </div>
    </div>
</x-app-layout>

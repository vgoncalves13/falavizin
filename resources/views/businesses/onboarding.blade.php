<x-app-layout title="Configuração — {{ $business->name }}">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ $business->canonicalUrl() }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar ao perfil
        </a>

        <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
            <div>
                <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">
                    Complete o perfil de {{ $business->name }}
                </h1>
                <p class="text-sm text-stone-500 mt-1">
                    Confirme as informações para que os moradores encontrem dados corretos sobre o seu estabelecimento.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-stone-100 text-stone-600">
                <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                {{ $business->localNeighborhood->name }}
            </span>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <livewire:business.business-onboarding-wizard :business="$business" :key="'wizard-'.$business->id" />
        </div>
    </div>
</x-app-layout>

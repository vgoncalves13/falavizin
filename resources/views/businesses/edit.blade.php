<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('businesses.show', $business) }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar ao perfil
        </a>

        <h1 class="text-2xl font-bold text-stone-900 mb-6" style="font-family: var(--font-display)">Editar Negócio</h1>

        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <livewire:business.business-form :business="$business" :neighborhood="$neighborhood" />
        </div>
    </div>
</x-app-layout>

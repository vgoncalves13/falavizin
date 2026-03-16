<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Serviços e Comércios</h1>
            @auth
                <a href="{{ route('businesses.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Cadastrar Negócio
                </a>
            @endauth
        </div>

        {{-- Lista de negócios virá na Semana 3 --}}
        <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
            <x-heroicon-o-building-storefront class="w-10 h-10 text-stone-300 mx-auto mb-3" />
            <p class="text-stone-500">Nenhum negócio cadastrado ainda.</p>
        </div>
    </div>
</x-app-layout>

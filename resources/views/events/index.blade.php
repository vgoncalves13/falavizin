<x-app-layout title="Eventos">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Eventos</h1>
                <p class="text-sm text-stone-500 mt-1">Acompanhe os eventos do bairro</p>
            </div>
            @auth
                <a href="{{ route('feed.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Criar evento
                </a>
            @endauth
        </div>

        <livewire:events.event-list :neighborhood="$neighborhood" />
    </div>
</x-app-layout>

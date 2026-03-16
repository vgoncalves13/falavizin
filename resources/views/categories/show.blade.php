<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-3 mb-6">
            <x-dynamic-component :component="'heroicon-o-' . $category->icon" class="w-6 h-6 text-amber-600" />
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">
                {{ $category->name }}
            </h1>
        </div>

        {{-- Conteúdo filtrado por categoria virá nas Semanas 2 e 3 --}}
        <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
            <p class="text-stone-500">Conteúdo desta categoria em desenvolvimento.</p>
        </div>
    </div>
</x-app-layout>

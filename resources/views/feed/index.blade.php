<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Feed do Bairro</h1>
            @auth
                <a href="{{ route('feed.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Publicar
                </a>
            @endauth
        </div>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        <livewire:feed.feed-list />
    </div>
</x-app-layout>

<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                {{-- Feed list Livewire component virá aqui na Semana 2 --}}
                <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                    <x-heroicon-o-newspaper class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                    <p class="text-stone-500">Nenhum post ainda. Seja o primeiro a publicar!</p>
                    @auth
                        <a href="{{ route('feed.create') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-amber-600 hover:text-amber-700">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Criar post
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Sidebar de categorias -->
            <div>
                <h2 class="text-base font-semibold text-stone-900 mb-3">Filtrar por categoria</h2>
                <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
                    <div class="p-1">
                        <a href="{{ route('feed.index') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-amber-50 text-amber-700">
                            Todos os posts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

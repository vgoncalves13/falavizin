<x-app-layout title="Editar post">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('feed.show', $post) }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar ao post
        </a>

        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h1 class="text-lg font-semibold text-stone-900 mb-5" style="font-family: var(--font-display)">
                Editar post
            </h1>

            <livewire:feed.edit-post :post="$post" />
        </div>
    </div>
</x-app-layout>

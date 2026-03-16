<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('feed.index') }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar ao Feed
        </a>

        <article class="bg-white rounded-xl border border-stone-200 p-6">
            <h1 class="text-2xl font-bold text-stone-900 mb-4" style="font-family: var(--font-display)">
                {{ $post->title }}
            </h1>
            <div class="prose prose-stone max-w-none">
                {!! nl2br(e($post->body)) !!}
            </div>
        </article>

        {{-- Seção de comentários e votos virão na Semana 2 --}}
    </div>
</x-app-layout>

<x-app-layout :title="$post->title" :description="Str::limit($post->body, 160)">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('feed.index') }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar ao Feed
        </a>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        <article class="bg-white rounded-xl border border-stone-200 p-6 mb-6">
            {{-- Meta --}}
            <div class="flex items-center gap-2 flex-wrap mb-4">
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                    <span class="text-xs font-bold text-amber-700">{{ substr($post->user->name, 0, 1) }}</span>
                </div>
                <a href="{{ route('users.show', $post->user) }}"
                   class="text-sm font-medium text-stone-900 hover:text-amber-700 transition-colors">{{ $post->user->name }}</a>
                <span class="text-stone-300">·</span>
                <span class="text-xs text-stone-400">{{ $post->created_at->diffForHumans() }}</span>
                @if($post->location)
                    <span class="text-stone-300">·</span>
                    <span class="inline-flex items-center gap-0.5 text-xs text-stone-400">
                        <x-heroicon-o-map-pin class="w-3 h-3" />
                        {{ $post->location }}
                    </span>
                @endif
                <x-category-badge :category="$post->category" />
            </div>

            <h1 class="text-2xl font-bold text-stone-900 mb-4 leading-snug" style="font-family: var(--font-display)">
                {{ $post->title }}
            </h1>

            <div class="text-stone-700 text-sm leading-relaxed whitespace-pre-line">{{ $post->body }}</div>

            {{-- Votos --}}
            <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                <livewire:feed.vote-buttons :post="$post" :key="'votes-'.$post->id" />

                <div class="flex items-center gap-3">
                    <x-share-button :url="route('feed.show', $post)" :title="$post->title" />

                    @auth
                        @unless(auth()->id() === $post->user_id)
                            <x-report-modal
                                :action="route('report.post', $post)"
                                trigger-class="inline-flex items-center gap-1 text-xs text-stone-400 hover:text-amber-600 transition-colors"
                                trigger-label="Reportar"
                            />
                        @endunless
                    @endauth

                    @can('delete', $post)
                        <form action="{{ route('feed.destroy', $post) }}" method="POST"
                              onsubmit="return confirm('Remover este post?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 text-xs text-stone-400 hover:text-red-500 transition-colors">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Remover
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </article>

        {{-- Comentários --}}
        <livewire:feed.comment-section :post="$post" :key="'comments-'.$post->id" />
    </div>
</x-app-layout>

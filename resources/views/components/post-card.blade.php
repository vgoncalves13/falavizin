@props(['post'])

<article
    x-data
    @click="window.location.href = '{{ route('feed.show', $post) }}'"
    class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition-shadow duration-200 cursor-pointer"
>
    <div class="flex items-start gap-4">
        {{-- Avatar --}}
        <div class="shrink-0 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
            <span class="text-sm font-bold text-amber-700">{{ substr($post->user->name, 0, 1) }}</span>
        </div>

        {{-- Conteúdo --}}
        <div class="flex-1 min-w-0">
            {{-- Meta --}}
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <a href="{{ route('users.show', $post->user) }}"
                   class="text-sm font-medium text-stone-900 hover:text-amber-700 transition-colors"
                   @click.stop>{{ $post->user->name }}</a>
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

            {{-- Título --}}
            <h3 class="font-semibold text-stone-900 mb-1.5 leading-snug">
                <a href="{{ route('feed.show', $post) }}" class="hover:text-amber-700 transition-colors duration-150">
                    {{ $post->title }}
                </a>
            </h3>

            {{-- Prévia do body --}}
            <p class="text-sm text-stone-600 line-clamp-2 mb-3">{{ $post->body }}</p>

            {{-- Footer: stats --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('feed.show', $post) }}"
                   class="inline-flex items-center gap-1.5 text-xs text-stone-400 hover:text-stone-600 transition-colors">
                    <x-heroicon-o-chat-bubble-left class="w-4 h-4" />
                    {{ $post->comments_count ?? 0 }}
                </a>
                <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                    <x-heroicon-o-hand-thumb-up class="w-4 h-4" />
                    {{ $post->votes_count ?? 0 }}
                </span>
            </div>
        </div>
    </div>
</article>

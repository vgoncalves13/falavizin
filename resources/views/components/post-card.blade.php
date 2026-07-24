@props(['post'])

<article
    x-data
    @click="window.location.href = '{{ $post->canonicalUrl() }}'"
    class="bg-white rounded-xl border
        {{ $post->is_sponsored ? 'border-amber-300 bg-amber-50/30' : '' }}
        {{ !$post->is_sponsored && $post->category?->slug === 'pedido' ? 'border-blue-200 bg-blue-50/20' : '' }}
        {{ !$post->is_sponsored && $post->category?->slug !== 'pedido' ? 'border-stone-200' : '' }}
        hover:shadow-md transition-shadow duration-200 cursor-pointer overflow-hidden"
>
    {{-- Post image --}}
    @if($post->image)
        <div class="h-44 overflow-hidden">
            <img
                src="{{ Storage::url($post->image) }}"
                alt="{{ $post->title }}"
                class="w-full h-full object-cover"
            />
        </div>
    @endif

    <div class="p-5">
        <div class="flex items-start gap-4">
            {{-- Avatar --}}
            <x-avatar :user="$post->user" class="w-10 h-10 text-sm" />

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
                    @if($post->serviceCategory)
                        <span class="inline-flex items-center gap-1 text-xs text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
                            <x-heroicon-o-wrench-screwdriver class="w-3 h-3" />
                            {{ $post->serviceCategory->name }}
                        </span>
                    @endif
                    @if($post->is_sponsored)
                        <span class="inline-flex items-center gap-0.5 text-xs font-medium text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded-full">
                            <x-heroicon-s-bolt class="w-3 h-3" />
                            Patrocinado
                        </span>
                    @endif
                </div>

                {{-- Event date --}}
                @if($post->event_starts_at)
                    <div class="flex items-center gap-1.5 text-xs text-amber-700 font-medium mb-2">
                        <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                        {{ $post->event_starts_at->isoFormat('D [de] MMMM, HH:mm') }}
                    </div>
                @endif

                {{-- Título --}}
                <h3 class="font-semibold text-stone-900 mb-1.5 leading-snug">
                    <a href="{{ $post->canonicalUrl() }}" class="hover:text-amber-700 transition-colors duration-150">
                        {{ $post->title }}
                    </a>
                </h3>

                {{-- Prévia do body --}}
                <p class="text-sm text-stone-600 line-clamp-2 mb-3">{{ $post->body }}</p>

                {{-- Footer: stats --}}
                <div class="flex items-center gap-4 flex-wrap">
                    <a href="{{ $post->canonicalUrl() }}"
                       class="inline-flex items-center gap-1.5 text-xs text-stone-400 hover:text-stone-600 transition-colors">
                        <x-heroicon-o-chat-bubble-left class="w-4 h-4" />
                        {{ $post->comments_count ?? 0 }}
                    </a>
                    <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                        <x-heroicon-o-hand-thumb-up class="w-4 h-4" />
                        {{ $post->votes_count ?? 0 }}
                    </span>
                    @if($post->poll)
                        <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                            <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                            Enquete
                        </span>
                    @endif
                    @if($post->category?->slug === 'problema' && $post->resolution_status !== null)
                        @if($post->resolution_status->value === 'resolvido')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full">
                                <x-heroicon-s-check-circle class="w-3 h-3" />
                                Resolvido
                            </span>
                        @elseif($post->resolution_status->value === 'em_andamento')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
                                <x-heroicon-o-arrow-path class="w-3 h-3" />
                                Em andamento
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</article>

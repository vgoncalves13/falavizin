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

        {{-- Status banner for author --}}
        @auth
            @if(auth()->id() === $post->user_id && $post->status->value !== 'approved')
                @if($post->status->value === 'pending')
                    <div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-clock class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-amber-800">Aguardando aprovação</p>
                                <p class="text-xs text-amber-600 mt-0.5">
                                    Seu post está na fila de moderação. Ele ficará visível no feed após aprovação do administrador.
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif($post->status->value === 'rejected')
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-x-circle class="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800">Post rejeitado</p>
                                <p class="text-xs text-red-600 mt-0.5">
                                    Seu post não foi aprovado pelo administrador. Você pode editar e reenviar para moderação.
                                </p>
                                <a href="{{ route('feed.edit', $post) }}"
                                   class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition-colors">
                                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                    Editar e reenviar
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        @endauth

        <article class="bg-white rounded-xl border {{ $post->is_sponsored ? 'border-amber-300' : 'border-stone-200' }} mb-6 overflow-hidden">
            {{-- Post image --}}
            @if($post->image)
                <div class="w-full max-h-80 overflow-hidden">
                    <img
                        src="{{ Storage::url($post->image) }}"
                        alt="{{ $post->title }}"
                        class="w-full object-cover"
                    />
                </div>
            @endif

            <div class="p-6">
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
                    @if($post->is_sponsored)
                        <span class="inline-flex items-center gap-0.5 text-xs font-medium text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded-full">
                            <x-heroicon-s-bolt class="w-3 h-3" />
                            Patrocinado
                        </span>
                    @endif
                </div>

                {{-- Event dates --}}
                @if($post->event_starts_at)
                    <div class="flex items-start gap-3 mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <x-heroicon-o-calendar-days class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-amber-800">
                                {{ $post->event_starts_at->isoFormat('dddd, D [de] MMMM [de] YYYY [às] HH:mm') }}
                            </p>
                            @if($post->event_ends_at)
                                <p class="text-xs text-amber-600 mt-0.5">
                                    até {{ $post->event_ends_at->isoFormat('D [de] MMMM [às] HH:mm') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <h1 class="text-2xl font-bold text-stone-900 mb-4 leading-snug" style="font-family: var(--font-display)">
                    {{ $post->title }}
                </h1>

                <div class="text-stone-700 text-sm leading-relaxed whitespace-pre-line">{{ $post->body }}</div>

                {{-- Poll --}}
                @if($post->poll)
                    <livewire:feed.poll-vote :poll="$post->poll" :key="'poll-'.$post->poll->id" />
                @endif

                {{-- Status de resolução (apenas para categoria Problema) --}}
                @if($post->category?->slug === 'problema')
                    <div class="mt-4 pt-4 border-t border-stone-100">
                        @can('update', $post)
                            <livewire:feed.post-status-toggle :post="$post" :key="'status-'.$post->id" />
                        @else
                            @if($post->resolution_status !== null)
                                <div class="flex items-center gap-2">
                                    @if($post->resolution_status->value === 'resolvido')
                                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg">
                                            <x-heroicon-s-check-circle class="w-4 h-4" />
                                            Problema resolvido
                                            @if($post->resolved_at)
                                                <span class="text-green-500 font-normal text-xs">· {{ $post->resolved_at->diffForHumans() }}</span>
                                            @endif
                                        </span>
                                    @elseif($post->resolution_status->value === 'em_andamento')
                                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg">
                                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                                            Em andamento
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @endcan
                    </div>
                @endif

                {{-- Votos --}}
                <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <livewire:feed.vote-buttons :post="$post" :key="'votes-'.$post->id" />
                        <livewire:feed.save-button :post="$post" :key="'save-'.$post->id" />
                    </div>

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

                        @can('update', $post)
                            <a href="{{ route('feed.edit', $post) }}"
                               class="inline-flex items-center gap-1.5 text-xs text-stone-400 hover:text-amber-600 transition-colors">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                Editar
                            </a>
                        @endcan

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
            </div>
        </article>

        {{-- Comentários --}}
        <livewire:feed.comment-section :post="$post" :key="'comments-'.$post->id" />

        {{-- Posts relacionados --}}
        @if($relatedPosts->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-base font-semibold text-stone-700 mb-3 flex items-center gap-2">
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-stone-400" />
                    Mais em <x-category-badge :category="$post->category" />
                </h2>
                <div class="space-y-3">
                    @foreach($relatedPosts as $related)
                        <x-post-card :post="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

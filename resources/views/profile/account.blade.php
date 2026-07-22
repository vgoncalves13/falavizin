<x-app-layout title="Minha Conta">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @session('success')
            <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        {{-- Header do perfil --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                        <span class="text-xl font-bold text-amber-700">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">
                            {{ $user->name }}
                        </h1>
                        <p class="text-sm text-stone-500">{{ $user->email }}</p>
                        @if($user->neighborhood)
                            <p class="inline-flex items-center gap-1 text-xs text-stone-400 mt-0.5">
                                <x-heroicon-o-map-pin class="w-3 h-3" />
                                {{ $user->neighborhood }}
                            </p>
                        @endif
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-stone-600 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors">
                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                    Editar dados
                </a>
            </div>

            {{-- Estatísticas rápidas --}}
            <div class="mt-5 pt-5 border-t border-stone-100 grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-stone-900">{{ $user->posts_count }}</p>
                    <p class="text-xs text-stone-500 mt-0.5">Posts</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-900">{{ $user->businesses_count }}</p>
                    <p class="text-xs text-stone-500 mt-0.5">Negócios</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-900">{{ $user->comments_count }}</p>
                    <p class="text-xs text-stone-500 mt-0.5">Comentários</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ tab: @js($activeTab) }">
            <div class="flex gap-1 bg-white rounded-xl border border-stone-200 p-1 mb-5 overflow-x-auto">
                <button
                    @click="tab = 'posts'"
                    :class="tab === 'posts' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100'"
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 whitespace-nowrap"
                >
                    <x-heroicon-o-newspaper class="w-4 h-4" />
                    Meus Posts
                    <span class="text-xs opacity-75">({{ $user->posts_count }})</span>
                </button>
                <button
                    @click="tab = 'businesses'"
                    :class="tab === 'businesses' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100'"
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 whitespace-nowrap"
                >
                    <x-heroicon-o-building-storefront class="w-4 h-4" />
                    Negócios
                    <span class="text-xs opacity-75">({{ $user->businesses_count }})</span>
                </button>
                <button
                    @click="tab = 'favorites'"
                    :class="tab === 'favorites' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100'"
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 whitespace-nowrap"
                >
                    <x-heroicon-o-heart class="w-4 h-4" />
                    Favoritos
                    <span class="text-xs opacity-75">({{ $user->favorites_count }})</span>
                </button>
                <button
                    @click="tab = 'comments'"
                    :class="tab === 'comments' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100'"
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 whitespace-nowrap"
                >
                    <x-heroicon-o-chat-bubble-left class="w-4 h-4" />
                    Comentários
                    <span class="text-xs opacity-75">({{ $user->comments_count }})</span>
                </button>
                <button
                    @click="tab = 'saved'"
                    :class="tab === 'saved' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100'"
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 whitespace-nowrap"
                >
                    <x-heroicon-o-bookmark class="w-4 h-4" />
                    Salvos
                    <span class="text-xs opacity-75">({{ $user->saved_posts_count }})</span>
                </button>
                <button
                    @click="tab = 'notifications'"
                    :class="tab === 'notifications' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100'"
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 whitespace-nowrap"
                >
                    <x-heroicon-o-bell class="w-4 h-4" />
                    Notificações
                </button>
                @if($businessCategoryIds->isNotEmpty())
                    <button
                        @click="tab = 'requests'"
                        :class="tab === 'requests' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100'"
                        class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 whitespace-nowrap"
                    >
                        <x-heroicon-o-megaphone class="w-4 h-4" />
                        Pedidos
                    </button>
                @endif
            </div>

            {{-- Posts --}}
            <div x-show="tab === 'posts'">
                @if($posts->isEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-12 text-center">
                        <x-heroicon-o-newspaper class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                        <p class="text-stone-500 text-sm">Você ainda não publicou nenhum post.</p>
                        <a href="{{ route('feed.create') }}"
                           class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Publicar no feed
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($posts as $post)
                            <div class="bg-white rounded-xl border border-stone-200 p-4 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <x-category-badge :category="$post->category" />
                                        <span class="text-xs text-stone-400">{{ $post->created_at->diffForHumans() }}</span>
                                        @if($post->status->value === 'pending')
                                            <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Aguardando aprovação</span>
                                        @elseif($post->status->value === 'rejected')
                                            <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">Rejeitado</span>
                                            <a href="{{ route('feed.edit', $post) }}"
                                               class="text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 px-2 py-0.5 rounded-full transition-colors">
                                                Editar e reenviar
                                            </a>
                                        @endif
                                    </div>
                                    <a href="{{ route('feed.show', $post) }}"
                                       class="font-medium text-stone-900 hover:text-amber-600 transition-colors line-clamp-1">
                                        {{ $post->title }}
                                    </a>
                                    <p class="text-sm text-stone-500 line-clamp-1 mt-0.5">{{ $post->body }}</p>
                                </div>
                                <form action="{{ route('feed.destroy', $post) }}" method="POST" class="shrink-0"
                                      onsubmit="return confirm('Remover este post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-stone-300 hover:text-red-500 transition-colors">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    {{ $posts->links() }}
                @endif
            </div>

            {{-- Negócios --}}
            <div x-show="tab === 'businesses'" style="display: none;">
                @if($businesses->isEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-12 text-center">
                        <x-heroicon-o-building-storefront class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                        <p class="text-stone-500 text-sm">Você ainda não cadastrou nenhum negócio.</p>
                        <a href="{{ route('businesses.create') }}"
                           class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Cadastrar negócio
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($businesses as $business)
                            <div class="bg-white rounded-xl border border-stone-200 p-4 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <x-category-badge :category="$business->category" />
                                        @if($business->plan->value === 'featured')
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                                <x-heroicon-s-star class="w-3 h-3" />
                                                Destaque
                                            </span>
                                        @endif
                                        @if($business->status->value === 'pending')
                                            <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Aguardando aprovação</span>
                                        @elseif($business->status->value === 'rejected')
                                            <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">Rejeitado</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('businesses.show', $business) }}"
                                       class="font-medium text-stone-900 hover:text-amber-600 transition-colors">
                                        {{ $business->name }}
                                    </a>
                                    <p class="text-xs text-stone-400 mt-0.5 flex items-center gap-1">
                                        <x-heroicon-o-map-pin class="w-3 h-3" />
                                        {{ $business->neighborhood }}
                                    </p>
                                </div>
                                <a href="{{ route('businesses.edit', $business) }}"
                                   class="shrink-0 text-stone-300 hover:text-amber-600 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                    {{ $businesses->links() }}
                @endif
            </div>

            {{-- Favoritos --}}
            <div x-show="tab === 'favorites'" style="display: none;">
                @if($favorites->isEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-12 text-center">
                        <x-heroicon-o-heart class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                        <p class="text-stone-500 text-sm">Você ainda não favoritou nenhum negócio.</p>
                        <a href="{{ route('businesses.index') }}"
                           class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Explorar serviços
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($favorites as $business)
                            <x-business-card :business="$business" />
                        @endforeach
                    </div>
                    {{ $favorites->links() }}
                @endif
            </div>

            {{-- Comentários --}}
            <div x-show="tab === 'comments'" style="display: none;">
                @if($comments->isEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-12 text-center">
                        <x-heroicon-o-chat-bubble-left class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                        <p class="text-stone-500 text-sm">Você ainda não fez nenhum comentário.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($comments as $comment)
                            <div class="bg-white rounded-xl border border-stone-200 p-4">
                                <a href="{{ route('feed.show', $comment->post) }}"
                                   class="text-xs font-medium text-amber-600 hover:text-amber-700 mb-2 inline-block transition-colors">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3 inline mr-0.5" />
                                    {{ $comment->post->title }}
                                </a>
                                <p class="text-sm text-stone-700">{{ $comment->body }}</p>
                                <p class="text-xs text-stone-400 mt-1.5">{{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                    {{ $comments->links() }}
                @endif
            </div>
            {{-- Salvos --}}
            <div x-show="tab === 'saved'" style="display: none;">
                @if($savedPosts->isEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-12 text-center">
                        <x-heroicon-o-bookmark class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                        <p class="text-stone-500 text-sm">Você ainda não salvou nenhum post.</p>
                        <a href="{{ route('feed.index') }}"
                           class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Explorar o feed
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($savedPosts as $post)
                            <div class="bg-white rounded-xl border border-stone-200 p-4 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <x-category-badge :category="$post->category" />
                                        <span class="text-xs text-stone-400">{{ $post->created_at->diffForHumans() }}</span>
                                        <span class="text-xs text-stone-400">por {{ $post->user->name ?? '' }}</span>
                                    </div>
                                    <a href="{{ route('feed.show', $post) }}"
                                       class="font-medium text-stone-900 hover:text-amber-600 transition-colors line-clamp-1">
                                        {{ $post->title }}
                                    </a>
                                    <p class="text-sm text-stone-500 line-clamp-1 mt-0.5">{{ $post->body }}</p>
                                </div>
                                <a href="{{ route('feed.show', $post) }}"
                                   class="shrink-0 text-stone-300 hover:text-amber-600 transition-colors">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                    {{ $savedPosts->links() }}
                @endif
            </div>

            {{-- Notificações --}}
            <div x-show="tab === 'notifications'" style="display: none;">
                <div class="bg-white rounded-xl border border-stone-200 p-6">
                    <livewire:profile.notification-settings />
                </div>
            </div>

            {{-- Pedidos relevantes --}}
            @if($businessCategoryIds->isNotEmpty())
                <div x-show="tab === 'requests'" style="display: none;">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
                            <p class="text-sm text-blue-700">
                                Pedidos de moradores que combinam com as categorias dos seus negócios. Responda diretamente no post para oferecer seus serviços.
                            </p>
                        </div>
                    </div>
                    @if($relevantRequests->isEmpty())
                        <div class="bg-white rounded-xl border border-stone-200 p-12 text-center">
                            <x-heroicon-o-megaphone class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                            <p class="text-stone-500 text-sm">Nenhum pedido relevante no momento.</p>
                            <p class="text-xs text-stone-400 mt-1">Quando alguém publicar um pedido nas categorias dos seus negócios, ele aparecerá aqui.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($relevantRequests as $post)
                                <div class="bg-white rounded-xl border border-blue-200 p-4 hover:shadow-sm transition-shadow">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
                                                    <x-heroicon-o-wrench-screwdriver class="w-3 h-3" />
                                                    {{ $post->serviceCategory->name }}
                                                </span>
                                                <span class="text-xs text-stone-400">{{ $post->created_at->diffForHumans() }}</span>
                                                <span class="text-xs text-stone-400">por {{ $post->user->name }}</span>
                                            </div>
                                            <a href="{{ route('feed.show', $post) }}"
                                               class="font-medium text-stone-900 hover:text-amber-600 transition-colors">
                                                {{ $post->title }}
                                            </a>
                                            <p class="text-sm text-stone-500 line-clamp-2 mt-0.5">{{ $post->body }}</p>
                                            <div class="flex items-center gap-3 mt-2">
                                                <span class="inline-flex items-center gap-1 text-xs text-stone-400">
                                                    <x-heroicon-o-chat-bubble-left class="w-3.5 h-3.5" />
                                                    {{ $post->comments_count }} respostas
                                                </span>
                                                <span class="inline-flex items-center gap-1 text-xs text-stone-400">
                                                    <x-heroicon-o-hand-thumb-up class="w-3.5 h-3.5" />
                                                    {{ $post->votes_count }}
                                                </span>
                                            </div>
                                        </div>
                                        <a href="{{ route('feed.show', $post) }}"
                                           class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition-colors">
                                            <x-heroicon-o-chat-bubble-left-right class="w-4 h-4" />
                                            Responder
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{ $relevantRequests->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

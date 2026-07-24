<x-app-layout title="Posts Patrocinados">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Posts Patrocinados</h1>
                <p class="text-sm text-stone-500 mt-0.5">
                    {{ $sponsoredCount }} {{ $sponsoredCount === 1 ? 'post patrocinado' : 'posts patrocinados' }} no momento
                </p>
            </div>
            <a href="{{ route('admin.moderation.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-600 hover:text-stone-800 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Moderação
            </a>
        </div>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        {{-- Busca --}}
        <form method="GET" action="{{ route('admin.sponsored-posts.index') }}" class="mb-5">
            <div class="relative">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-stone-400" />
                </div>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Buscar por título ou autor..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                />
            </div>
        </form>

        {{-- Lista --}}
        @if($posts->isEmpty())
            <div class="bg-white rounded-xl border border-stone-200 p-10 text-center">
                <x-heroicon-o-newspaper class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                <p class="text-stone-500 text-sm">Nenhum post aprovado encontrado.</p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-stone-200 divide-y divide-stone-100 overflow-hidden">
                @foreach($posts as $post)
                    <div class="flex items-center gap-4 px-5 py-4 {{ $post->is_sponsored ? 'bg-amber-50/50' : '' }}">

                        {{-- Status indicator --}}
                        <div class="shrink-0">
                            @if($post->is_sponsored)
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100">
                                    <x-heroicon-s-bolt class="w-4 h-4 text-amber-600" />
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-stone-100">
                                    <x-heroicon-o-bolt class="w-4 h-4 text-stone-400" />
                                </span>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ $post->canonicalUrl() }}"
                                   target="_blank"
                                   class="font-medium text-stone-900 hover:text-amber-700 transition-colors truncate max-w-sm">
                                    {{ $post->title }}
                                </a>
                                @if($post->is_sponsored)
                                    <span class="shrink-0 text-xs font-medium text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded-full">
                                        Patrocinado
                                    </span>
                                @endif
                            </div>
                            <div class="mt-0.5 flex items-center gap-2 text-xs text-stone-400">
                                <span>{{ $post->user->name }}</span>
                                <span>·</span>
                                <x-category-badge :category="$post->category" />
                                <span>·</span>
                                <span>{{ $post->created_at->format('d/m/Y') }}</span>
                                <span>·</span>
                                <span>{{ $post->comments_count }} {{ $post->comments_count === 1 ? 'comentário' : 'comentários' }}</span>
                            </div>
                        </div>

                        {{-- Toggle --}}
                        @if($post->is_sponsored)
                            <div class="shrink-0 flex items-center gap-2">
                                @if($post->sponsored_until)
                                    <span class="text-xs text-stone-500">
                                        Até {{ $post->sponsored_until->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-stone-500">Sem prazo</span>
                                @endif
                                <form action="{{ route('admin.sponsored-posts.toggle', $post) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium rounded-lg border bg-amber-500 border-amber-500 text-white hover:bg-amber-600 transition-colors">
                                        <x-heroicon-s-bolt class="w-3.5 h-3.5" />
                                        Remover
                                    </button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('admin.sponsored-posts.toggle', $post) }}" method="POST" class="shrink-0 flex items-center gap-2" x-data="{ days: '' }">
                                @csrf
                                <input type="hidden" name="days" :value="days">
                                <select
                                    x-model="days"
                                    class="text-xs rounded border-stone-300 text-stone-600 focus:ring-amber-500 focus:border-amber-500 py-1.5"
                                >
                                    <option value="">Sem prazo</option>
                                    <option value="7">7 dias</option>
                                    <option value="15">15 dias</option>
                                    <option value="30">30 dias</option>
                                    <option value="60">60 dias</option>
                                    <option value="90">90 dias</option>
                                </select>
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium rounded-lg border bg-white border-stone-300 text-stone-600 hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition-colors">
                                    <x-heroicon-o-bolt class="w-3.5 h-3.5" />
                                    Patrocinar
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-5">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

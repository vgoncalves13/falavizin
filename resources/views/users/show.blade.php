<x-app-layout :title="$user->name">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header do perfil --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-6">
            <div class="flex items-center gap-5">
                <div class="shrink-0 w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center">
                    <span class="text-2xl font-bold text-amber-700">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">
                        {{ $user->name }}
                    </h1>
                    <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-stone-500">
                        @if($user->neighborhood)
                            <span class="inline-flex items-center gap-1">
                                <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                                {{ $user->neighborhood }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1">
                            <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                            Membro desde {{ $user->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
                <div class="shrink-0 flex gap-6 text-center">
                    <div>
                        <p class="text-xl font-bold text-stone-900">{{ $posts->total() }}</p>
                        <p class="text-xs text-stone-400 mt-0.5">{{ Str::plural('post', $posts->total()) }}</p>
                    </div>
                    @if($businesses->total() > 0)
                        <div>
                            <p class="text-xl font-bold text-stone-900">{{ $businesses->total() }}</p>
                            <p class="text-xs text-stone-400 mt-0.5">{{ Str::plural('negócio', $businesses->total()) }}</p>
                        </div>
                    @endif
                    @if($user->points > 0)
                        <div>
                            <p class="text-xl font-bold text-amber-600">{{ number_format($user->points) }}</p>
                            <p class="text-xs text-stone-400 mt-0.5">pontos</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($posts->total() === 0 && $businesses->total() === 0)
            <div class="bg-white rounded-xl border border-stone-200 px-6 py-16 text-center">
                <x-heroicon-o-user class="w-10 h-10 mx-auto mb-3 text-stone-300" />
                <p class="text-stone-400 text-sm">Este usuário ainda não publicou nada.</p>
            </div>
        @else
            <div class="space-y-8">
                {{-- Negócios --}}
                @if($businesses->total() > 0)
                    <div>
                        <h2 class="text-base font-semibold text-stone-700 mb-3 flex items-center gap-2">
                            <x-heroicon-o-building-storefront class="w-4 h-4 text-stone-400" />
                            Negócios
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($businesses as $business)
                                <x-business-card :business="$business" />
                            @endforeach
                        </div>
                        {{ $businesses->links() }}
                    </div>
                @endif

                {{-- Posts --}}
                @if($posts->total() > 0)
                    <div>
                        <h2 class="text-base font-semibold text-stone-700 mb-3 flex items-center gap-2">
                            <x-heroicon-o-newspaper class="w-4 h-4 text-stone-400" />
                            Posts no Feed
                        </h2>
                        <div class="space-y-3">
                            @foreach($posts as $post)
                                <x-post-card :post="$post" />
                            @endforeach
                        </div>
                        {{ $posts->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-app-layout>

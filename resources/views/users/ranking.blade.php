<x-app-layout title="Ranking do Bairro">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Ranking do Bairro</h1>
            <p class="mt-1 text-sm text-stone-500">Moradores mais ativos e contribuintes da comunidade.</p>
        </div>

        @if($users->isEmpty())
            <div class="bg-white rounded-xl border border-stone-200 p-10 text-center">
                <x-heroicon-o-trophy class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                <p class="text-stone-500">Nenhum morador com pontos ainda. Seja o primeiro!</p>
                @auth
                    <a href="{{ route('feed.create') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-amber-600 hover:text-amber-700">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Publicar no feed
                    </a>
                @endauth
            </div>
        @else
            {{-- Top 3 --}}
            @if($users->count() >= 3)
                <div class="grid grid-cols-3 gap-3 mb-6">
                    @foreach($users->take(3) as $i => $topUser)
                        @php
                            $rank = $i + 1;
                            $sizes = ['col-span-1 order-2', 'col-span-1 order-1', 'col-span-1 order-3'];
                            $heights = ['py-8', 'py-5', 'py-4'];
                            $trophyColors = ['text-amber-500', 'text-stone-400', 'text-amber-700'];
                            $bgColors = ['bg-amber-50 border-amber-200', 'bg-white border-stone-200', 'bg-white border-stone-200'];
                        @endphp
                        <div class="{{ $sizes[$i] }} {{ $bgColors[$i] }} border rounded-xl {{ $heights[$i] }} flex flex-col items-center justify-center text-center px-3">
                            @if($rank === 1)
                                <x-heroicon-s-trophy class="w-6 h-6 {{ $trophyColors[$i] }} mb-1.5" />
                            @else
                                <span class="text-xl font-bold text-stone-400 mb-1.5">#{{ $rank }}</span>
                            @endif
                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center mb-1.5">
                                <span class="text-base font-bold text-amber-700">{{ substr($topUser->name, 0, 1) }}</span>
                            </div>
                            <a href="{{ route('users.show', $topUser) }}" class="text-sm font-semibold text-stone-900 hover:text-amber-600 truncate max-w-full">
                                {{ $topUser->name }}
                            </a>
                            @if($topUser->neighborhood)
                                <p class="text-xs text-stone-400 mt-0.5 truncate max-w-full">{{ $topUser->neighborhood }}</p>
                            @endif
                            <p class="mt-2 text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                {{ number_format($topUser->points) }} pts
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Resto do ranking --}}
            <div class="bg-white rounded-xl border border-stone-200 divide-y divide-stone-100">
                @foreach($users->skip(3) as $position => $user)
                    @php $rank = $position + 4; @endphp
                    <div class="flex items-center gap-4 px-5 py-3.5">
                        <span class="w-6 text-center text-sm font-medium text-stone-400 shrink-0">{{ $rank }}</span>
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                            <span class="text-sm font-bold text-amber-700">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('users.show', $user) }}" class="text-sm font-medium text-stone-900 hover:text-amber-600 truncate block">
                                {{ $user->name }}
                            </a>
                            @if($user->neighborhood)
                                <p class="text-xs text-stone-400 truncate">{{ $user->neighborhood }}</p>
                            @endif
                        </div>
                        <span class="text-sm font-semibold text-amber-600 shrink-0">{{ number_format($user->points) }} pts</span>
                    </div>
                @endforeach

                @if($users->count() <= 3)
                    {{-- Quando há menos de 4 usuários, mostra todos na lista simples --}}
                    @foreach($users as $position => $user)
                        @php $rank = $position + 1; @endphp
                        <div class="flex items-center gap-4 px-5 py-3.5">
                            <span class="w-6 text-center text-sm font-bold {{ $rank === 1 ? 'text-amber-500' : 'text-stone-400' }} shrink-0">#{{ $rank }}</span>
                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-amber-700">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('users.show', $user) }}" class="text-sm font-medium text-stone-900 hover:text-amber-600 truncate block">
                                    {{ $user->name }}
                                </a>
                                @if($user->neighborhood)
                                    <p class="text-xs text-stone-400 truncate">{{ $user->neighborhood }}</p>
                                @endif
                            </div>
                            <span class="text-sm font-semibold text-amber-600 shrink-0">{{ number_format($user->points) }} pts</span>
                        </div>
                    @endforeach
                @endif
            </div>

            @auth
                @php $authPoints = auth()->user()->points; @endphp
                @if($authPoints > 0 && !$users->contains('id', auth()->id()))
                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl px-5 py-3.5 flex items-center gap-3">
                        <x-heroicon-o-user class="w-4 h-4 text-amber-600 shrink-0" />
                        <p class="text-sm text-amber-800">
                            Você ainda não aparece no top 50. Continue contribuindo para subir no ranking!
                        </p>
                        <span class="ml-auto text-sm font-semibold text-amber-600 shrink-0">{{ number_format($authPoints) }} pts</span>
                    </div>
                @endif
            @endauth
        @endif
    </div>
</x-app-layout>

<x-app-layout title="Estatísticas">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Estatísticas</h1>
            <a href="{{ route('admin.moderation.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-600 hover:text-stone-800 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Moderação
            </a>
        </div>

        {{-- Cards de totais --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Usuários</span>
                    <x-heroicon-o-users class="w-4 h-4 text-stone-300" />
                </div>
                <p class="text-3xl font-bold text-stone-900">{{ number_format($totals['users']) }}</p>
                @if($thisWeek['users'] > 0)
                    <p class="mt-1 text-xs text-green-600 font-medium">+{{ $thisWeek['users'] }} esta semana</p>
                @else
                    <p class="mt-1 text-xs text-stone-400">Nenhum novo esta semana</p>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Posts</span>
                    <x-heroicon-o-newspaper class="w-4 h-4 text-stone-300" />
                </div>
                <p class="text-3xl font-bold text-stone-900">{{ number_format($totals['posts']) }}</p>
                @if($thisWeek['posts'] > 0)
                    <p class="mt-1 text-xs text-green-600 font-medium">+{{ $thisWeek['posts'] }} esta semana</p>
                @else
                    <p class="mt-1 text-xs text-stone-400">Nenhum novo esta semana</p>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Negócios</span>
                    <x-heroicon-o-building-storefront class="w-4 h-4 text-stone-300" />
                </div>
                <p class="text-3xl font-bold text-stone-900">{{ number_format($totals['businesses']) }}</p>
                @if($thisWeek['businesses'] > 0)
                    <p class="mt-1 text-xs text-green-600 font-medium">+{{ $thisWeek['businesses'] }} esta semana</p>
                @else
                    <p class="mt-1 text-xs text-stone-400">Nenhum novo esta semana</p>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Comentários</span>
                    <x-heroicon-o-chat-bubble-left class="w-4 h-4 text-stone-300" />
                </div>
                <p class="text-3xl font-bold text-stone-900">{{ number_format($totals['comments']) }}</p>
            </div>
        </div>

        {{-- Gráfico de barras semanal --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-6">
            <h2 class="text-sm font-semibold text-stone-700 mb-5">Atividade nas últimas 8 semanas</h2>

            @php
                $maxPosts      = $weeks->max('posts');
                $maxBusinesses = $weeks->max('businesses');
                $maxUsers      = $weeks->max('users');
                $overallMax    = max($maxPosts, $maxBusinesses, $maxUsers, 1);
            @endphp

            <div class="flex items-end gap-2 h-40">
                @foreach($weeks as $week)
                    @php
                        $postH  = $week['posts']      ? max(4, round(($week['posts']      / $overallMax) * 100)) : 0;
                        $bizH   = $week['businesses'] ? max(4, round(($week['businesses'] / $overallMax) * 100)) : 0;
                        $userH  = $week['users']       ? max(4, round(($week['users']      / $overallMax) * 100)) : 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-0.5">
                        {{-- Barras agrupadas --}}
                        <div class="w-full flex items-end justify-center gap-0.5" style="height: 120px">
                            <div class="flex-1 rounded-t"
                                 style="height: {{ $postH }}%; background-color: #d97706;"
                                 title="Posts: {{ $week['posts'] }}"></div>
                            <div class="flex-1 rounded-t"
                                 style="height: {{ $bizH }}%; background-color: #a78bfa;"
                                 title="Negócios: {{ $week['businesses'] }}"></div>
                            <div class="flex-1 rounded-t"
                                 style="height: {{ $userH }}%; background-color: #34d399;"
                                 title="Usuários: {{ $week['users'] }}"></div>
                        </div>
                        <span class="text-[10px] text-stone-400 mt-1 truncate w-full text-center">{{ $week['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Legenda --}}
            <div class="flex items-center gap-5 mt-4 pt-4 border-t border-stone-100">
                <span class="flex items-center gap-1.5 text-xs text-stone-500">
                    <span class="inline-block w-2.5 h-2.5 rounded-sm" style="background-color: #d97706;"></span>
                    Posts
                </span>
                <span class="flex items-center gap-1.5 text-xs text-stone-500">
                    <span class="inline-block w-2.5 h-2.5 rounded-sm" style="background-color: #a78bfa;"></span>
                    Negócios
                </span>
                <span class="flex items-center gap-1.5 text-xs text-stone-500">
                    <span class="inline-block w-2.5 h-2.5 rounded-sm" style="background-color: #34d399;"></span>
                    Usuários
                </span>
            </div>
        </div>

        {{-- Tabela detalhada --}}
        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-stone-100">
                <h2 class="text-sm font-semibold text-stone-700">Detalhe por semana</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-100 bg-stone-50">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-stone-500 uppercase tracking-wide">Semana</th>
                        <th class="text-right px-5 py-2.5 text-xs font-semibold text-amber-600 uppercase tracking-wide">Posts</th>
                        <th class="text-right px-5 py-2.5 text-xs font-semibold uppercase tracking-wide" style="color: #a78bfa;">Negócios</th>
                        <th class="text-right px-5 py-2.5 text-xs font-semibold text-emerald-600 uppercase tracking-wide">Usuários</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @foreach($weeks->reverse()->values() as $week)
                        <tr class="{{ $loop->first ? 'bg-amber-50/30' : 'hover:bg-stone-50' }} transition-colors">
                            <td class="px-5 py-2.5 font-medium text-stone-700">
                                {{ $week['label'] }}
                                @if($loop->first)
                                    <span class="ml-1.5 text-[10px] font-semibold text-amber-600 uppercase">atual</span>
                                @endif
                            </td>
                            <td class="px-5 py-2.5 text-right font-semibold text-stone-800">{{ $week['posts'] }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-stone-800">{{ $week['businesses'] }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-stone-800">{{ $week['users'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-stone-200 bg-stone-50">
                        <td class="px-5 py-2.5 text-xs font-semibold text-stone-500 uppercase">Total (8 sem.)</td>
                        <td class="px-5 py-2.5 text-right text-sm font-bold text-stone-900">{{ $weeks->sum('posts') }}</td>
                        <td class="px-5 py-2.5 text-right text-sm font-bold text-stone-900">{{ $weeks->sum('businesses') }}</td>
                        <td class="px-5 py-2.5 text-right text-sm font-bold text-stone-900">{{ $weeks->sum('users') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Fila de aprovação pendente --}}
        @php $totalPending = array_sum($pending); @endphp
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-stone-700">Fila de aprovação</h2>
                <a href="{{ route('admin.moderation.index') }}"
                   class="text-xs text-amber-600 hover:text-amber-700 font-medium transition-colors">
                    Ver painel →
                </a>
            </div>
            @if($totalPending === 0)
                <p class="text-sm text-stone-400">Nenhum item pendente.</p>
            @else
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-lg bg-stone-50 border border-stone-100 px-4 py-3 text-center">
                        <p class="text-2xl font-bold text-stone-900">{{ $pending['posts'] }}</p>
                        <p class="text-xs text-stone-500 mt-0.5">Posts</p>
                    </div>
                    <div class="rounded-lg bg-stone-50 border border-stone-100 px-4 py-3 text-center">
                        <p class="text-2xl font-bold text-stone-900">{{ $pending['businesses'] }}</p>
                        <p class="text-xs text-stone-500 mt-0.5">Negócios</p>
                    </div>
                    <div class="rounded-lg bg-stone-50 border border-stone-100 px-4 py-3 text-center">
                        <p class="text-2xl font-bold text-stone-900">{{ $pending['promotions'] }}</p>
                        <p class="text-xs text-stone-500 mt-0.5">Promoções</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

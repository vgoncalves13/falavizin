<div>
    <div class="flex items-end justify-between mb-4">
        <div>
            <p class="sec-eyebrow">Agenda</p>
            <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:800;color:#1c1917;margin:0;letter-spacing:-.02em;">Eventos</h2>
        </div>
        <a href="{{ route('neighborhood.events.index', $neighborhood->routeParameters()) }}"
           class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors mb-1">
            Ver tudo →
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        {{-- Header do calendário --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-stone-100">
            <button
                wire:click="previousMonth"
                class="p-1 rounded-lg hover:bg-stone-100 transition-colors"
                aria-label="Mês anterior"
            >
                <x-heroicon-o-chevron-left class="w-4 h-4 text-stone-500" />
            </button>
            <span class="text-sm font-semibold text-stone-900">
                {{ $month->isoFormat('MMMM [de] YYYY') }}
            </span>
            <button
                wire:click="nextMonth"
                class="p-1 rounded-lg hover:bg-stone-100 transition-colors"
                aria-label="Próximo mês"
            >
                <x-heroicon-o-chevron-right class="w-4 h-4 text-stone-500" />
            </button>
        </div>

        {{-- Dias da semana --}}
        <div class="grid grid-cols-7 border-b border-stone-100">
            @foreach(['D', 'S', 'T', 'Q', 'Q', 'S', 'S'] as $dayName)
                <div class="py-2 text-center text-xs font-medium text-stone-400">
                    {{ $dayName }}
                </div>
            @endforeach
        </div>

        {{-- Dias do mês --}}
        <div class="grid grid-cols-7 p-2">
            @foreach($days as $day)
                @if($day === null)
                    <div class="aspect-square"></div>
                @else
                    <button
                        wire:click="selectDate('{{ $day['date'] }}')"
                        class="aspect-square flex flex-col items-center justify-center rounded-lg transition-all duration-150
                            {{ $day['isToday'] ? 'bg-amber-600 text-white font-bold' : '' }}
                            {{ $day['hasEvents'] && !$day['isToday'] ? 'bg-amber-50 font-semibold text-amber-800' : '' }}
                            {{ !$day['hasEvents'] && !$day['isToday'] ? 'text-stone-600 hover:bg-stone-50' : '' }}
                            {{ $selectedDate === $day['date'] ? 'ring-2 ring-amber-500 ring-offset-1' : '' }}"
                    >
                        <span class="text-sm">{{ $day['day'] }}</span>
                        @if($day['hasEvents'])
                            <div class="flex gap-0.5 mt-0.5">
                                @for($i = 0; $i < min($day['eventCount'], 3); $i++)
                                    <span class="w-1 h-1 rounded-full {{ $day['isToday'] ? 'bg-white' : 'bg-amber-500' }}"></span>
                                @endfor
                            </div>
                        @endif
                    </button>
                @endif
            @endforeach
        </div>

        {{-- Eventos do dia selecionado --}}
        @if($selectedDate)
            <div class="border-t border-stone-100 px-4 py-3">
                <p class="text-xs font-medium text-stone-500 mb-2">
                    {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D [de] MMMM') }}
                </p>
                @if(count($selectedEvents) > 0)
                    <div class="space-y-2">
                        @foreach($selectedEvents as $event)
                            <a href="{{ $event['url'] }}"
                               class="flex items-center gap-2 p-2 rounded-lg hover:bg-amber-50 transition-colors group">
                                <span class="text-xs font-medium text-amber-600 shrink-0">
                                    {{ $event['time'] }}
                                </span>
                                <span class="text-sm text-stone-700 group-hover:text-amber-700 transition-colors truncate">
                                    {{ $event['title'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-stone-400">Nenhum evento neste dia.</p>
                @endif
            </div>
        @endif
    </div>
</div>

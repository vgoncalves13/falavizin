<div>
    {{-- Filtros --}}
    <div class="flex items-center gap-2 mb-6">
        <button
            wire:click="setFilter('upcoming')"
            :class="$wire.filter === 'upcoming' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150"
        >
            Próximos
        </button>
        <button
            wire:click="setFilter('past')"
            :class="$wire.filter === 'past' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150"
        >
            Passados
        </button>
        <button
            wire:click="setFilter('all')"
            :class="$wire.filter === 'all' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150"
        >
            Todos
        </button>
    </div>

    {{-- Lista de eventos --}}
    @if($events->isEmpty())
        <div class="bg-white rounded-xl border border-stone-200 p-12 text-center">
            <x-heroicon-o-calendar-days class="w-10 h-10 text-stone-300 mx-auto mb-3" />
            <p class="text-stone-500 text-sm">
                @if($filter === 'upcoming')
                    Nenhum evento próximo agendado.
                @elseif($filter === 'past')
                    Nenhum evento passado encontrado.
                @else
                    Nenhum evento encontrado.
                @endif
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($events as $event)
                <a href="{{ $event->canonicalUrl() }}"
                   class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    {{-- Header com data --}}
                    <div class="bg-amber-50 border-b border-amber-100 px-4 py-3 flex items-center gap-3">
                        <div class="text-center shrink-0">
                            <div class="text-xs font-medium text-amber-600 uppercase">
                                {{ $event->event_starts_at->isoFormat('MMM') }}
                            </div>
                            <div class="text-2xl font-bold text-amber-800 leading-none">
                                {{ $event->event_starts_at->format('d') }}
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-stone-900 truncate">
                                {{ $event->event_starts_at->isoFormat('dddd') }}
                            </div>
                            <div class="text-xs text-stone-500">
                                {{ $event->event_starts_at->format('H:i') }}
                                @if($event->event_ends_at)
                                    – {{ $event->event_ends_at->format('H:i') }}
                                @endif
                            </div>
                        </div>
                        @if($event->event_starts_at->isToday())
                            <span class="ml-auto shrink-0 text-xs font-medium text-green-700 bg-green-100 px-2 py-0.5 rounded-full">
                                Hoje
                            </span>
                        @endif
                    </div>

                    {{-- Conteúdo --}}
                    <div class="p-4">
                        <h3 class="font-medium text-stone-900 mb-1 line-clamp-2">
                            {{ $event->title }}
                        </h3>
                        @if($event->location)
                            <p class="flex items-center gap-1 text-xs text-stone-500">
                                <x-heroicon-o-map-pin class="w-3 h-3" />
                                {{ $event->location }}
                            </p>
                        @endif
                        <div class="mt-2 flex items-center gap-2 text-xs text-stone-400">
                            <span>{{ $event->user->name }}</span>
                            <span>·</span>
                            <span>{{ $event->comments_count }} {{ Str::plural('comentário', $event->comments_count) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @endif
</div>

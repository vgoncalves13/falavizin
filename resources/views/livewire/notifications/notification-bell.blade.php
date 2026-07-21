<div wire:poll.30s class="relative" x-data="{ open: false }" x-on:click.outside="open = false">

    {{-- Botão do sino --}}
    <button @click="open = !open"
            class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg text-stone-500 hover:bg-stone-100 hover:text-stone-700 transition-colors duration-150"
            aria-label="Notificações{{ $unreadCount > 0 ? ", {$unreadCount} não lidas" : '' }}"
            aria-expanded="false"
            :aria-expanded="open.toString()"
            aria-haspopup="true">
        <x-heroicon-o-bell class="w-5 h-5" aria-hidden="true" />
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-amber-600 rounded-full" aria-hidden="true">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-stone-200 z-50 overflow-hidden"
         style="display: none;"
         role="menu"
         aria-label="Menu de notificações">

        <div class="flex items-center justify-between px-4 py-3 border-b border-stone-100">
            <h3 class="text-sm font-semibold text-stone-900">Notificações</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllRead"
                        class="text-xs text-amber-600 hover:text-amber-700 font-medium transition-colors">
                    Marcar todas como lidas
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-stone-50">
            @forelse($notifications as $notification)
                @php $data = $notification->data; @endphp
                <div wire:key="{{ $notification->id }}"
                     class="flex items-start gap-3 px-4 py-3 {{ $notification->read_at ? 'bg-white' : 'bg-amber-50/50' }} hover:bg-stone-50 transition-colors cursor-default">

                    <div class="shrink-0 mt-0.5">
                        <x-dynamic-component :component="'heroicon-o-' . ($data['icon'] ?? 'bell')"
                                             class="w-5 h-5 {{ $data['color'] ?? 'text-stone-400' }}" />
                    </div>

                    <div class="flex-1 min-w-0">
                        @if(!empty($data['url']))
                            <a href="{{ $data['url'] }}"
                               wire:click="markRead('{{ $notification->id }}')"
                               class="text-sm text-stone-700 hover:text-amber-600 leading-snug line-clamp-2 transition-colors">
                                {{ $data['message'] ?? '' }}
                            </a>
                        @else
                            <p class="text-sm text-stone-700 leading-snug line-clamp-2">
                                {{ $data['message'] ?? '' }}
                            </p>
                        @endif
                        <p class="text-xs text-stone-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>

                    @if(!$notification->read_at)
                        <div class="shrink-0 mt-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500"></span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-stone-400">
                    <x-heroicon-o-bell-slash class="w-8 h-8 mx-auto mb-2 text-stone-300" />
                    Nenhuma notificação ainda
                </div>
            @endforelse
        </div>

        <div class="px-4 py-2.5 border-t border-stone-100 bg-stone-50/50">
            <a href="{{ route('notifications.index') }}"
               class="text-xs text-stone-500 hover:text-amber-600 font-medium transition-colors">
                Ver todas as notificações →
            </a>
        </div>
    </div>
</div>

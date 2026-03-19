<x-app-layout>
    <x-slot name="title">Notificações</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-stone-900 mb-6" style="font-family: var(--font-display)">
            Notificações
        </h1>

        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden divide-y divide-stone-100">
            @forelse($notifications as $notification)
                @php $data = $notification->data; @endphp
                <div class="flex items-start gap-4 px-5 py-4 {{ $notification->read_at ? 'bg-white' : 'bg-amber-50/40' }}">
                    <div class="shrink-0 mt-0.5">
                        <x-dynamic-component :component="'heroicon-o-' . ($data['icon'] ?? 'bell')"
                                             class="w-5 h-5 {{ $data['color'] ?? 'text-stone-400' }}" />
                    </div>
                    <div class="flex-1 min-w-0">
                        @if(!empty($data['url']))
                            <a href="{{ $data['url'] }}" class="text-sm text-stone-800 hover:text-amber-600 transition-colors">
                                {{ $data['message'] ?? '' }}
                            </a>
                        @else
                            <p class="text-sm text-stone-800">{{ $data['message'] ?? '' }}</p>
                        @endif
                        <p class="text-xs text-stone-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!$notification->read_at)
                        <span class="inline-block w-2 h-2 mt-2 rounded-full bg-amber-500 shrink-0"></span>
                    @endif
                </div>
            @empty
                <div class="px-5 py-16 text-center text-stone-400">
                    <x-heroicon-o-bell-slash class="w-10 h-10 mx-auto mb-3 text-stone-300" />
                    <p class="text-sm">Nenhuma notificação ainda.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

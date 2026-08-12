<x-app-layout title="Reivindicações">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Reivindicações</h1>
            <a href="{{ route('admin.moderation.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-600 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-lg transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Moderação
            </a>
        </div>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        {{-- Filtros --}}
        <div class="flex flex-wrap items-center gap-2 mb-5">
            @foreach(['pending', 'approved', 'rejected'] as $filter)
                <a href="{{ route('admin.claims.index', ['status' => $filter]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors border
                          {{ $status === $filter ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50' }}">
                    {{ match($filter) { 'pending' => 'Pendentes', 'approved' => 'Aprovadas', 'rejected' => 'Rejeitadas' } }}
                    ({{ $counts[$filter] }})
                </a>
            @endforeach

            <form method="GET" action="{{ route('admin.claims.index') }}" class="ml-auto flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}" />
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar negócio ou solicitante..."
                       class="rounded-lg border-stone-300 text-sm text-stone-900 focus:ring-amber-500 focus:border-amber-500" />
                <button type="submit" class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-lg">Buscar</button>
            </form>
        </div>

        <div class="space-y-3">
            @forelse($claims as $claim)
                <div class="bg-white rounded-xl border border-stone-200 p-5">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ $claim->business->canonicalUrl() }}" target="_blank"
                                   class="font-medium text-stone-900 hover:text-amber-600">{{ $claim->business->name }}</a>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                     {{ $claim->status->value === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($claim->status->value === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $claim->status->label() }}
                                </span>
                            </div>
                            <p class="text-xs text-stone-500 mt-1">
                                <a href="{{ route('users.show', $claim->user) }}" class="text-amber-600 hover:text-amber-700 font-medium">{{ $claim->user->name }}</a>
                                ({{ $claim->user->email }}) · {{ $claim->business->localNeighborhood->name ?? $claim->business->neighborhood }}
                            </p>
                            <p class="text-xs text-stone-400 mt-0.5">Solicitado {{ $claim->created_at->diffForHumans() }}</p>

                            @if($claim->message)
                                <p class="text-sm text-stone-600 mt-2 bg-stone-50 border border-stone-200 rounded-lg px-3 py-2">“{{ $claim->message }}”</p>
                            @endif

                            @if($claim->status->value === 'rejected' && $claim->rejection_reason)
                                <p class="text-xs text-red-600 mt-2">Motivo da rejeição: {{ $claim->rejection_reason }}</p>
                            @endif

                            @if($claim->status->value === 'approved' && $claim->reviewed_at)
                                <p class="text-xs text-stone-400 mt-0.5">Analisado por {{ $claim->reviewer?->name ?? 'admin' }} em {{ $claim->reviewed_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>

                        @if($claim->status->value === 'pending')
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.claims.approve', $claim) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Aprovar
                                    </button>
                                </form>
                                <form action="{{ route('admin.claims.reject', $claim) }}" method="POST"
                                      x-data="{ open: false }" @submit.prevent="open = true">
                                    @csrf
                                    <button type="button" @click="open = true"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        Rejeitar
                                    </button>
                                    <div x-show="open" x-cloak x-transition
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                                        <div class="bg-white rounded-xl p-5 w-full max-w-md" @click.outside="open = false">
                                            <p class="text-sm font-semibold text-stone-900">Rejeitar reivindicação</p>
                                            <p class="text-xs text-stone-500 mt-1">Informe o motivo (opcional) para o solicitante.</p>
                                            <textarea name="reason" rows="3" placeholder="Motivo da rejeição..."
                                                      class="mt-3 w-full rounded-lg border-stone-300 text-sm text-stone-900 focus:ring-red-500 focus:border-red-500"></textarea>
                                            <div class="mt-4 flex items-center justify-end gap-2">
                                                <button type="button" @click="open = false" class="text-sm text-stone-500 hover:text-stone-700">Cancelar</button>
                                                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg">Rejeitar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-stone-200 p-10 text-center">
                    <x-heroicon-o-inbox class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                    <p class="text-stone-500">Nenhuma reivindicação encontrada.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $claims->links() }}
        </div>
    </div>
</x-app-layout>

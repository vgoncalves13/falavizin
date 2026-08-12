<div class="space-y-4">
    @foreach($items as $item)
        @php $business = $item['business']; @endphp
        <div class="bg-white rounded-xl border border-amber-200 p-5" x-data="{ open: true }" x-show="open">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900">
                        Complete o perfil de {{ $business->name }}
                    </p>
                    <p class="text-xs text-stone-500 mt-1">
                        Confirme as informações para que os moradores encontrem dados corretos sobre o seu estabelecimento.
                    </p>
                </div>
                <button type="button" @click="open = false; $wire.dismiss({{ $business->id }})"
                        class="text-stone-400 hover:text-stone-600 shrink-0" aria-label="Fechar aviso">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-stone-500 mb-1.5">
                    <span>{{ $item['completed'] }} de {{ $item['total'] }} etapas concluídas</span>
                    <span class="font-semibold text-amber-700">{{ $item['percent'] }}%</span>
                </div>
                <div class="h-2 bg-stone-100 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full transition-all duration-300" style="width: {{ $item['percent'] }}%"></div>
                </div>
                @if($item['next'])
                    <p class="text-xs text-stone-500 mt-2">
                        Próxima etapa: <span class="font-medium text-stone-700">{{ $item['next']->label() }}</span>
                    </p>
                @endif
            </div>

            <div class="mt-4 flex items-center gap-2">
                <a href="{{ route('businesses.onboarding', $business) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                    Continuar configuração
                </a>
                <a href="{{ $business->canonicalUrl() }}" class="text-sm text-stone-500 hover:text-stone-700">
                    Ver perfil
                </a>
            </div>
        </div>
    @endforeach
</div>

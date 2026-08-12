<div>
    @if($show)
        <div class="bg-white rounded-xl border border-amber-200 p-5 mb-5" x-data="{ open: true }" x-show="open">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                    <x-heroicon-o-clipboard-document-check class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" />
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900">
                            Complete o perfil de {{ $business->name }}
                        </p>
                        <p class="text-xs text-stone-500 mt-1">
                            Confirme as informações para que os moradores encontrem dados corretos sobre o seu estabelecimento.
                        </p>

                        <div class="mt-3 max-w-sm">
                            <div class="flex items-center justify-between text-xs text-stone-500 mb-1">
                                <span>{{ $completed }} de {{ $total }} etapas concluídas</span>
                                <span class="font-semibold text-amber-700">{{ $percent }}%</span>
                            </div>
                            <div class="h-1.5 bg-stone-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                            </div>
                            @if($next)
                                <p class="text-xs text-stone-500 mt-1.5">
                                    Próxima etapa: <span class="font-medium text-stone-700">{{ $next->label() }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('businesses.onboarding', $business) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-arrow-right class="w-4 h-4" />
                                Continuar configuração
                            </a>
                        </div>
                    </div>
                </div>
                <button type="button" @click="open = false; $wire.dismiss()"
                        class="text-stone-400 hover:text-stone-600 shrink-0" aria-label="Fechar aviso">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif
</div>

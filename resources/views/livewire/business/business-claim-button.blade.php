<div id="reivindicar">
    @if($canManage)
        <div class="bg-white rounded-xl border border-stone-200 p-5 mb-5">
            <div class="flex items-center gap-3">
                <x-heroicon-o-check-badge class="w-6 h-6 text-amber-600 shrink-0" />
                <div>
                    <p class="text-sm font-semibold text-stone-900">Você administra este estabelecimento</p>
                    <p class="text-xs text-stone-500 mt-0.5">Acesse a <a href="{{ route('businesses.onboarding', $business) }}" class="text-amber-600 hover:text-amber-700 font-medium">configuração do perfil</a> para manter os dados atualizados.</p>
                </div>
            </div>
        </div>
    @elseif($pendingClaim)
        @if($justSubmitted)
            <div class="bg-white rounded-xl border border-green-200 p-5 mb-5">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-stone-900">Solicitação enviada</p>
                        <p class="text-xs text-stone-500 mt-1">Nossa equipe analisará sua solicitação. Você será avisado quando o acesso ao estabelecimento for aprovado.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-amber-200 p-5 mb-5">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-clock class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-stone-900">Reivindicação em análise</p>
                        <p class="text-xs text-stone-500 mt-1">Sua solicitação para administrar este estabelecimento ainda está sendo analisada.</p>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-white rounded-xl border border-stone-200 p-5 mb-5">
            <p class="text-sm font-semibold text-stone-900">Este negócio é seu?</p>
            <p class="text-xs text-stone-500 mt-1">
                Reivindique o perfil para confirmar as informações, adicionar fotos e manter seus clientes atualizados.
            </p>

            <div class="mt-4">
                @if($showForm)
                    <div class="rounded-lg bg-stone-50 border border-stone-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-stone-900">{{ $business->name }}</p>
                                <p class="text-xs text-stone-500 mt-0.5">
                                    {{ $business->neighborhood }}{{ $business->address ? ' · '.$business->address : '' }}
                                </p>
                            </div>
                            <button type="button" wire:click="$set('showForm', false)" class="text-stone-400 hover:text-stone-600 shrink-0">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="mt-4">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="confirm"
                                       class="mt-0.5 rounded border-stone-300 text-amber-600 focus:ring-amber-500" />
                                <span class="text-xs text-stone-700">
                                    Confirmo que represento este estabelecimento e estou autorizado a gerenciá-lo no FalaVizin.
                                </span>
                            </label>
                            @error('confirm') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-3">
                            <textarea wire:model="message" rows="2"
                                      placeholder="Mensagem opcional para ajudar nossa equipe na análise"
                                      class="w-full rounded-lg border-stone-300 text-sm text-stone-900 focus:ring-amber-500 focus:border-amber-500"></textarea>
                            @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-3 flex justify-end">
                            <button type="button" wire:click="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors">
                                <span wire:loading.remove>Enviar solicitação</span>
                                <span wire:loading>Enviando...</span>
                            </button>
                        </div>
                    </div>
                @else
                    <button type="button" wire:click="start"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <x-heroicon-o-flag class="w-4 h-4" />
                        Reivindicar este negócio
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

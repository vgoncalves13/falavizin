@props(['business'])

@if($business->plan->value === 'free')
    <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl border border-amber-200 p-6">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                <x-heroicon-s-star class="w-5 h-5 text-amber-600" />
            </div>
            <div>
                <h3 class="font-semibold text-stone-900">Plano Destaque</h3>
                <p class="text-sm text-stone-600">Destaque seu negócio no bairro</p>
            </div>
        </div>

        <div class="space-y-3 mb-5">
            <div class="flex items-start gap-2.5">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-stone-800">Apareça primeiro nas buscas</p>
                    <p class="text-xs text-stone-500">Seu negócio aparece antes dos demais</p>
                </div>
            </div>
            <div class="flex items-start gap-2.5">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-stone-800">Badge "Destaque" visível</p>
                    <p class="text-xs text-stone-500">Selo dourado no card e perfil do negócio</p>
                </div>
            </div>
            <div class="flex items-start gap-2.5">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-stone-800">Apareça na Home do bairro</p>
                    <p class="text-xs text-stone-500">Exibido na seção "Negócios em destaque"</p>
                </div>
            </div>
            <div class="flex items-start gap-2.5">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-stone-800">Mais promoções</p>
                    <p class="text-xs text-stone-500">Publique promoções semanalmente (free: 1/mês)</p>
                </div>
            </div>
        </div>

        @if($business->plan_upgrade_requested_at)
            <div class="flex items-center gap-2 p-3 bg-amber-100 rounded-lg">
                <x-heroicon-o-clock class="w-4 h-4 text-amber-700" />
                <p class="text-sm text-amber-800">Solicitação enviada em {{ $business->plan_upgrade_requested_at->format('d/m/Y') }}. Aguardando aprovação.</p>
            </div>
        @else
            <form action="{{ route('businesses.upgrade.request', $business) }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-s-star class="w-4 h-4" />
                    Solicitar plano Destaque
                </button>
            </form>
            <p class="text-xs text-stone-500 text-center mt-2">Aprovação pelo admin em até 48h</p>
        @endif
    </div>
@else
    <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl border border-amber-300 p-6">
        <div class="flex items-center gap-3 mb-3">
            <x-heroicon-s-star class="w-6 h-6 text-amber-500" />
            <div>
                <h3 class="font-semibold text-stone-900">Plano Destaque</h3>
                <p class="text-xs text-green-700">Seu negócio está em destaque!</p>
            </div>
        </div>
        <div class="text-sm text-stone-600">
            <p>Seu negócio aparece primeiro nas buscas e na página inicial do bairro.</p>
        </div>
    </div>
@endif

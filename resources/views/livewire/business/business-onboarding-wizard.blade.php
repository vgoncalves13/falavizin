<div>
    @session('onboarding_flash')
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ $value }}
        </div>
    @endsession

    <div class="flex items-center justify-between text-xs text-stone-500 mb-1.5">
        <span>{{ $completedCount }} de {{ count(\App\Enums\BusinessOnboardingStep::ordered()) }} etapas concluídas</span>
        <span class="font-semibold text-amber-700">{{ $percent }}%</span>
    </div>
    <div class="h-2 bg-stone-100 rounded-full overflow-hidden mb-6">
        <div class="h-full bg-amber-500 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
    </div>

    {{-- Passos --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach(\App\Enums\BusinessOnboardingStep::ordered() as $index => $step)
            @php
                $status = collect($steps)->firstWhere('step', $step);
                $isCurrent = $currentStep === $step->value;
            @endphp
            <button type="button" wire:click="gotoStep('{{ $step->value }}')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-colors border
                           {{ $status['completed'] ? 'bg-green-50 text-green-700 border-green-200' : ($isCurrent ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50') }}">
                <span class="w-4 h-4 rounded-full inline-flex items-center justify-center text-[10px]
                             {{ $status['completed'] ? 'bg-green-500 text-white' : ($isCurrent ? 'bg-white text-amber-700' : 'bg-stone-200 text-stone-600') }}">
                    {{ $index + 1 }}
                </span>
                {{ $step->label() }}
            </button>
        @endforeach
    </div>

    {{-- Etapa: dados básicos --}}
    @if($currentStep === \App\Enums\BusinessOnboardingStep::BasicDetails->value)
        <div class="rounded-lg border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-900">Confirmar dados básicos</h3>
            <p class="text-xs text-stone-500 mt-1">Confirme se as informações abaixo estão corretas.</p>

            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Nome</dt><dd class="font-medium text-stone-900">{{ $business->name }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Endereço</dt><dd class="text-stone-700">{{ $business->address ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Telefone</dt><dd class="text-stone-700">{{ $business->phone ? implode(', ', $business->phone) : '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500">WhatsApp</dt><dd class="text-stone-700">{{ $business->whatsapp ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Instagram</dt><dd class="text-stone-700">{{ $business->instagram ? '@'.$business->instagram : '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Site</dt><dd class="text-stone-700">{{ $business->website ?? '—' }}</dd></div>
            </dl>

            @if($business->description)
                <p class="text-xs text-stone-500 mt-4">Descrição atual: <span class="text-stone-700">{{ $business->description }}</span></p>
            @endif

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="button" wire:click="confirmBasicDetails" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-check class="w-4 h-4" /> Confirmar dados
                </button>
                <a href="{{ route('neighborhood.businesses.edit', [...$business->localNeighborhood->routeParameters(), 'business' => $business]) }}"
                   class="text-sm text-stone-500 hover:text-stone-700">
                    Corrigir dados
                </a>
            </div>
        </div>
    @endif

    {{-- Etapa: horários --}}
    @if($currentStep === \App\Enums\BusinessOnboardingStep::OpeningHours->value)
        <div class="rounded-lg border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-900">Confirmar horários de funcionamento</h3>
            <p class="text-xs text-stone-500 mt-1">Confirme os horários ou ajuste os dias de funcionamento e fechados.</p>

            <div class="mt-4 rounded-lg border border-stone-200 overflow-hidden divide-y divide-stone-100">
                @foreach($openingHours as $i => $hours)
                    <div class="flex items-center gap-3 px-4 py-2.5 bg-white">
                        <span class="w-28 text-sm text-stone-600 shrink-0">{{ $hours['day'] }}</span>
                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                            <input type="checkbox" wire:model.live="openingHours.{{ $i }}.closed"
                                   class="rounded border-stone-300 text-amber-600 focus:ring-amber-500" />
                            <span class="text-xs text-stone-500">Fechado</span>
                        </label>
                        @if(! $hours['closed'])
                            <div class="flex items-center gap-2 ml-auto">
                                <input type="time" wire:model="openingHours.{{ $i }}.open"
                                       class="rounded border-stone-300 text-stone-900 text-sm py-1 px-2" />
                                <span class="text-stone-400 text-sm">–</span>
                                <input type="time" wire:model="openingHours.{{ $i }}.close"
                                       class="rounded border-stone-300 text-stone-900 text-sm py-1 px-2" />
                            </div>
                        @else
                            <span class="ml-auto text-xs text-stone-400 italic">Fechado</span>
                        @endif
                    </div>
                @endforeach
            </div>
            @error('openingHours.*.open') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="mt-5">
                <button type="button" wire:click="saveOpeningHours" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-check class="w-4 h-4" /> Confirmar horários
                </button>
            </div>
        </div>
    @endif

    {{-- Etapa: foto própria --}}
    @if($currentStep === \App\Enums\BusinessOnboardingStep::OwnPhoto->value)
        <div class="rounded-lg border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-900">Adicionar foto própria</h3>
            <p class="text-xs text-stone-500 mt-1">Envie pelo menos uma foto tirada ou produzida por você. Fotos importadas de fontes externas não contam.</p>

            <div class="mt-4">
                <input type="file" wire:model="newPhotos" multiple accept="image/*"
                       class="w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                <div wire:loading wire:target="newPhotos" class="mt-1 text-xs text-stone-400">Carregando...</div>
                @error('newPhotos.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($newPhotos as $photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="h-16 w-20 object-cover rounded-lg border border-stone-200" alt="Preview" />
                    @endforeach
                </div>
            </div>

            <div class="mt-5">
                <button type="button" wire:click="uploadPhotos" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-cloud-arrow-up class="w-4 h-4" /> Enviar foto
                </button>
            </div>
        </div>
    @endif

    {{-- Etapa: produtos e serviços --}}
    @if($currentStep === \App\Enums\BusinessOnboardingStep::ProductsServices->value)
        <div class="rounded-lg border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-900">Produtos e serviços</h3>
            <p class="text-xs text-stone-500 mt-1">Explique claramente o que você oferece aos moradores.</p>

            <div class="mt-4">
                <textarea wire:model="description" rows="5"
                          placeholder="Ex: Padaria artesanal com pães integrais, bolos por encomenda e café da manhã todos os dias."
                          class="w-full rounded-lg border-stone-300 text-sm text-stone-900 focus:ring-amber-500 focus:border-amber-500"></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5">
                <button type="button" wire:click="saveServices" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-check class="w-4 h-4" /> Salvar
                </button>
            </div>
        </div>
    @endif

    {{-- Etapa: ação inicial --}}
    @if($currentStep === \App\Enums\BusinessOnboardingStep::InitialAction->value)
        <div class="rounded-lg border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-900">Escolha uma ação inicial</h3>
            <p class="text-xs text-stone-500 mt-1">Escolha uma ação para movimentar seu perfil. Promoções não são obrigatórias.</p>

            <div class="mt-4 grid sm:grid-cols-2 gap-3">
                @foreach(\App\Actions\CompleteBusinessInitialAction::eligibleActions() as $action)
                    <button type="button" wire:click="completeAction('{{ $action['key'] }}')"
                            class="text-left p-4 rounded-lg border border-stone-200 bg-white hover:border-amber-300 hover:bg-amber-50 transition-colors">
                        <span class="text-sm font-medium text-stone-900">{{ $action['label'] }}</span>
                    </button>
                @endforeach
            </div>
            <p class="text-xs text-stone-400 mt-3">
                Novidades, eventos e promoções concluem a etapa automaticamente após a publicação. O compartilhamento é registrado ao usar o botão de compartilhar no seu perfil, e o QR Code é confirmado na página de download.
            </p>
        </div>
    @endif

    {{-- Completo --}}
    @if($currentStep === '')
        <div class="rounded-lg border border-green-200 bg-green-50 p-8 text-center">
            <x-heroicon-o-check-circle class="w-10 h-10 text-green-500 mx-auto mb-3" />
            <h3 class="font-semibold text-stone-900">Configuração concluída!</h3>
            <p class="text-sm text-stone-600 mt-1">O perfil de {{ $business->name }} está completo.</p>
            <a href="{{ $business->canonicalUrl() }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg">
                Ver perfil público
            </a>
        </div>
    @endif
</div>

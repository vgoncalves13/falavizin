<x-app-layout :title="$business->name" :description="Str::limit($business->description ?? $business->name . ' — ' . $business->neighborhood, 160)">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('businesses.index') }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar aos Serviços
        </a>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession
        @session('error')
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        {{-- Header / Cover --}}
        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden mb-5">
            @if($business->coverPhoto)
                <div class="aspect-video max-h-72 overflow-hidden">
                    <img
                        src="{{ Storage::url($business->coverPhoto->path) }}"
                        alt="{{ $business->name }}"
                        class="w-full h-full object-cover"
                    />
                </div>
            @endif

            <div class="p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">
                                {{ $business->name }}
                            </h1>
                            @if($business->plan->value === 'featured')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-300">
                                    <x-heroicon-s-star class="w-3 h-3" />
                                    Destaque
                                </span>
                            @endif
                        </div>
                        <x-category-badge :category="$business->category" />
                    </div>

                    <div class="flex items-center gap-2">
                        @can('update', $business)
                            <a href="{{ route('businesses.edit', $business) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-stone-600 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                Editar
                            </a>
                        @endcan
                        @auth
                            @if(! $business->claimed)
                                <form action="{{ route('businesses.claim.request', $business) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition-colors">
                                        <x-heroicon-o-flag class="w-4 h-4" />
                                        Reivindicar
                                    </button>
                                </form>
                            @endif
                            @cannot('update', $business)
                                <x-report-modal
                                    :action="route('report.business', $business)"
                                    trigger-class="inline-flex items-center gap-1 text-xs text-stone-400 hover:text-amber-600 transition-colors"
                                    trigger-label="Reportar"
                                />
                            @endcannot
                        @endauth
                    </div>
                </div>

                {{-- Localização --}}
                <div class="mt-4 flex items-center gap-1.5 text-sm text-stone-500">
                    <x-heroicon-o-map-pin class="w-4 h-4 shrink-0" />
                    <span>{{ $business->neighborhood }}{{ $business->city ? ', ' . $business->city : '' }}</span>
                </div>

                {{-- Descrição --}}
                @if($business->description)
                    <p class="mt-4 text-stone-700 leading-relaxed">{{ $business->description }}</p>
                @endif

                {{-- Contato --}}
                @if($business->phone || $business->whatsapp || $business->website)
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        @foreach($business->phone ?? [] as $phoneNumber)
                            @if($phoneNumber)
                                <a href="tel:{{ $phoneNumber }}"
                                   class="inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-stone-800">
                                    <x-heroicon-o-phone class="w-4 h-4" />
                                    {{ $phoneNumber }}
                                </a>
                            @endif
                        @endforeach

                        @if($business->whatsapp)
                            <x-whatsapp-button :phone="$business->whatsapp" message="Olá! Vi seu perfil no Hub do Bairro." />
                        @endif

                        @if($business->website)
                            <a href="{{ $business->website }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-sm text-amber-600 hover:text-amber-700">
                                <x-heroicon-o-globe-alt class="w-4 h-4" />
                                Site
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Horários de funcionamento --}}
                @php
                    $openDays = collect($business->opening_hours ?? [])->filter(fn($h) => ! ($h['closed'] ?? true));
                @endphp
                @if($business->opening_hours && $openDays->isNotEmpty())
                    <div class="mt-5" x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                                class="flex items-center gap-2 text-sm font-medium text-stone-700 hover:text-stone-900 transition-colors">
                            <x-heroicon-o-clock class="w-4 h-4 text-stone-400" />
                            Horários de funcionamento
                            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-stone-400 transition-transform duration-200"
                                                        ::class="open ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="open" x-transition class="mt-3 rounded-lg border border-stone-100 overflow-hidden divide-y divide-stone-50">
                            @foreach($business->opening_hours as $hours)
                                <div class="flex items-center justify-between px-4 py-2 text-sm
                                            {{ ($hours['closed'] ?? true) ? 'bg-stone-50 text-stone-400' : 'bg-white text-stone-700' }}">
                                    <span class="font-medium">{{ $hours['day'] }}</span>
                                    @if($hours['closed'] ?? true)
                                        <span class="text-xs italic">Fechado</span>
                                    @else
                                        <span>{{ $hours['open'] }} – {{ $hours['close'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Galeria de fotos --}}
        <livewire:business.photo-gallery :business="$business" :key="'gallery-'.$business->id" />

        {{-- Promoções --}}
        @if($business->promotions->isNotEmpty() || auth()->user()?->can('update', $business))
            <div class="bg-white rounded-xl border border-stone-200 p-6 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-stone-900">Promoções ativas</h2>
                </div>

                @if($business->promotions->isNotEmpty())
                    <div class="space-y-3 mb-4">
                        @foreach($business->promotions as $promotion)
                            <div class="flex items-start justify-between gap-3 p-4 bg-amber-50 rounded-lg border border-amber-200">
                                <div>
                                    <p class="font-medium text-stone-900">{{ $promotion->title }}</p>
                                    @if($promotion->description)
                                        <p class="text-sm text-stone-600 mt-0.5">{{ $promotion->description }}</p>
                                    @endif
                                    @if($promotion->ends_at)
                                        <p class="text-xs text-stone-400 mt-1">
                                            Válido até {{ $promotion->ends_at->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>
                                @can('update', $business)
                                    <form action="{{ route('promotions.destroy', $promotion) }}" method="POST" class="shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-stone-400 hover:text-red-500 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-stone-400 mb-4">Nenhuma promoção ativa no momento.</p>
                @endif

                @can('update', $business)
                    <livewire:business.promotion-form :business="$business" />
                @endcan
            </div>
        @endif
    </div>
</x-app-layout>

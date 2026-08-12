<x-app-layout :title="$business->name" :description="Str::limit($business->description ?? $business->name . ' — ' . $business->neighborhood, 160)">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('neighborhood.businesses.index', $business->localNeighborhood->routeParameters()) }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar aos Serviços
        </a>

        @unless($business->localNeighborhood->is_active)
            <x-inactive-neighborhood-banner :neighborhood="$business->localNeighborhood" />
            @push('head')
                <meta name="robots" content="noindex,follow">
            @endpush
        @endunless

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
                <div class="h-56 w-full overflow-hidden sm:h-72">
                    <img
                        src="{{ Storage::url($business->coverPhoto->path) }}"
                        alt="{{ $business->name }}"
                        class="block h-full w-full object-cover"
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
                            @if($business->isVerified())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200"
                                      title="Os dados essenciais deste estabelecimento foram confirmados pelo responsável.">
                                    <x-heroicon-s-check-badge class="w-3 h-3" />
                                    Perfil verificado
                                </span>
                            @endif
                            @if($business->is_founder)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-300"
                                      title="Participou da fase inicial de construção da comunidade FalaVizin no {{ $business->localNeighborhood->name }}.">
                                    <x-heroicon-s-shield-check class="w-3 h-3" />
                                    Comércio Fundador
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @foreach($business->categories as $category)
                                <x-category-badge :category="$category" />
                            @endforeach
                        </div>

                        {{-- Confiança local --}}
                        @php $positiveReviews = $business->positiveReviewsCount(); @endphp
                        @if($positiveReviews >= 1)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                <x-heroicon-s-hand-thumb-up class="w-3.5 h-3.5" />
                                Recomendado por {{ $positiveReviews }} {{ $positiveReviews === 1 ? 'vizinho' : 'vizinhos' }}
                            </span>
                        @endif
                    </div>

                    @php $highlightShare = session('highlight_share') == $business->id; @endphp

                    @if($highlightShare)
                        <div class="w-full flex items-start gap-2 p-3 bg-amber-50 border border-amber-300 rounded-lg">
                            <x-heroicon-o-share class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-amber-800">Compartilhe seu perfil para concluir a configuração</p>
                                <p class="text-xs text-amber-700 mt-0.5">
                                    Use o botão <strong>Compartilhar</strong> destacado ao lado para divulgar o link do seu negócio.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <div class="{{ $highlightShare ? 'rounded-lg ring-2 ring-amber-400 ring-offset-2 bg-amber-50 animate-pulse' : '' }}">
                            <x-share-button :url="$business->canonicalUrl()" :title="$business->name"
                                            :track-url="auth()->user()?->can('update', $business) ? route('businesses.share.track', $business) : null" />
                        </div>

                        @auth
                            <livewire:business.favorite-button :business="$business" :key="'fav-'.$business->id" />
                        @endauth

                        @can('update', $business)
                            <a href="{{ route('neighborhood.businesses.edit', [...$business->localNeighborhood->routeParameters(), 'business' => $business]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-stone-600 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                Editar
                            </a>
                        @endcan
                        @can('delete', $business)
                            <form action="{{ route('neighborhood.businesses.destroy', [...$business->localNeighborhood->routeParameters(), 'business' => $business]) }}" method="POST"
                                  data-confirm="Tem certeza que deseja remover &quot;{{ $business->name }}&quot;? Esta ação não pode ser desfeita."
                                  onsubmit="return window.confirm(this.dataset.confirm)">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                    Remover
                                </button>
                            </form>
                        @endcan
                        @auth
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
                @if($business->phone || $business->whatsapp || $business->instagram || $business->website)
                    <div class="mt-5 flex flex-wrap items-center gap-3" x-data="businessContactTracking({{ $business->id }})">
                        @foreach($business->phone ?? [] as $phoneNumber)
                            @if($phoneNumber)
                                <a href="tel:{{ $phoneNumber }}"
                                   @click="trackContact('phone_click')"
                                   class="inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-stone-800">
                                    <x-heroicon-o-phone class="w-4 h-4" />
                                    {{ $phoneNumber }}
                                </a>
                            @endif
                        @endforeach

                        @if($business->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $business->whatsapp) }}?={{ urlencode('Olá! Vi seu perfil no FalaVizin.') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               @click="trackContact('whatsapp_click')"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                WhatsApp
                            </a>
                        @endif

                        @if($business->instagram)
                            <a href="https://www.instagram.com/{{ $business->instagram }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-tr from-amber-500 via-orange-500 to-pink-500 hover:opacity-90 text-white text-sm font-medium rounded-lg transition-opacity">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                                Instagram
                            </a>
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
                    @php $openNow = $business->isOpenNow(); @endphp
                    <div class="mt-5" x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                                class="flex items-center gap-2 text-sm font-medium text-stone-700 hover:text-stone-900 transition-colors">
                            <x-heroicon-o-clock class="w-4 h-4 text-stone-400" />
                            Horários de funcionamento
                            @if($openNow === true)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                    Aberto agora
                                </span>
                            @elseif($openNow === false)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-200">
                                    Fechado agora
                                </span>
                            @endif
                            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-stone-400 transition-transform duration-200"
                                                        ::class="open ? 'rotate-180' : ''" />
                        </button>
                        @php
                            $todayDayNames = [1=>'Segunda-feira',2=>'Terça-feira',3=>'Quarta-feira',4=>'Quinta-feira',5=>'Sexta-feira',6=>'Sábado',7=>'Domingo'];
                            $todayName = $todayDayNames[(int) now()->format('N')];
                        @endphp
                        <div x-show="open" x-transition class="mt-3 rounded-lg border border-stone-100 overflow-hidden divide-y divide-stone-50">
                            @foreach($business->opening_hours as $hours)
                                @php $isToday = ($hours['day'] === $todayName); @endphp
                                <div class="flex items-center justify-between px-4 py-2 text-sm
                                            {{ ($hours['closed'] ?? true) ? 'bg-stone-50 text-stone-400' : 'bg-white text-stone-700' }}
                                            {{ $isToday ? 'font-semibold' : '' }}">
                                    <span class="flex items-center gap-1.5">
                                        {{ $hours['day'] }}
                                        @if($isToday)
                                            <span class="text-xs font-normal text-stone-400">(hoje)</span>
                                        @endif
                                    </span>
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

        {{-- Onboarding do responsável --}}
        <livewire:business.onboarding-banner :business="$business" :key="'obanner-'.$business->id" />

        {{-- Reivindicação --}}
        @if(! $business->claimed && $business->acceptsCommunityInteractions())
            <livewire:business.business-claim-button :business="$business" :key="'claim-'.$business->id" />
        @endif

        {{-- Galeria de fotos --}}
        <livewire:business.photo-gallery :business="$business" :key="'gallery-'.$business->id" />

        {{-- Métricas e Plano Destaque --}}
        @can('update', $business)
            <div class="grid md:grid-cols-2 gap-5 mb-5">
                <div class="bg-white rounded-xl border border-stone-200 p-5">
                    <livewire:business.analytics-dashboard :business="$business" :key="'analytics-'.$business->id" />
                </div>
                <div>
                    <x-featured-benefits :business="$business" />
                </div>
            </div>
        @endcan

        {{-- Mapa --}}
        @if($business->lat && $business->lng)
            <div class="bg-white rounded-xl border border-stone-200 overflow-hidden mb-5">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-stone-100">
                    <x-heroicon-o-map-pin class="w-4 h-4 text-stone-400" />
                    <span class="text-sm font-medium text-stone-700">Localização</span>
                    @if($business->address)
                        <span class="text-sm text-stone-400">— {{ $business->address }}</span>
                    @endif
                </div>
                <div
                    id="business-map"
                    class="h-64 w-full z-0"
                    data-lat="{{ $business->lat }}"
                    data-lng="{{ $business->lng }}"
                    data-name="{{ $business->name }}"
                ></div>
            </div>
        @endif

        {{-- Promoções --}}
        @if(($business->promotions->isNotEmpty() || auth()->user()?->can('update', $business)) && $business->acceptsCommunityInteractions())
            <div class="bg-white rounded-xl border border-stone-200 p-6 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-stone-900">Promoções ativas</h2>
                </div>

                @if($business->promotions->isNotEmpty())
                    <div class="space-y-3 mb-4">
                        @foreach($business->promotions as $promotion)
                            <div class="flex items-start justify-between gap-3 p-4 {{ $promotion->status === 'pending' ? 'bg-stone-50 border-stone-200' : 'bg-amber-50 border-amber-200' }} rounded-lg border">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-medium text-stone-900">{{ $promotion->title }}</p>
                                        @if($promotion->status === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">
                                                Aguardando aprovação
                                            </span>
                                        @endif
                                    </div>
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
                                    <div class="shrink-0 flex items-center gap-2">
                                        <button
                                            type="button"
                                            onclick="Livewire.dispatch('edit-promotion', { id: {{ $promotion->id }} })"
                                            class="text-stone-400 hover:text-amber-600 transition-colors"
                                            title="Editar promoção"
                                        >
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </button>
                                        <form action="{{ route('promotions.destroy', $promotion) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-stone-400 hover:text-red-500 transition-colors"
                                                    onclick="return confirm('Remover esta promoção?')" title="Remover promoção">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-stone-400 mb-4">Nenhuma promoção ativa no momento.</p>
                @endif

                @can('update', $business)
                    @php
                        $lastPromotion = $business->promotions()->withTrashed()->latest()->first();
                        $nextAllowedAt = $lastPromotion ? $lastPromotion->created_at->addDays(7) : null;
                        $onCooldown = $nextAllowedAt && now()->lt($nextAllowedAt);
                    @endphp

                    @if($onCooldown)
                        <div class="flex items-start gap-3 p-4 bg-stone-50 border border-stone-200 rounded-lg">
                            <x-heroicon-o-clock class="w-5 h-5 text-stone-400 shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-stone-700">Limite semanal atingido</p>
                                <p class="text-xs text-stone-500 mt-0.5">
                                    Próxima promoção disponível em
                                    <span class="font-semibold text-stone-700">{{ $nextAllowedAt->format('d/m/Y \à\s H:i') }}</span>.
                                </p>
                            </div>
                        </div>
                    @else
                        <livewire:business.promotion-form :business="$business" />
                    @endif
                @endcan
            </div>
        @endif
        {{-- Avaliações --}}
        @if($business->acceptsCommunityInteractions())
            <div class="bg-white rounded-xl border border-stone-200 p-6 mb-5">
                <livewire:business.review-section :business="$business" :key="'reviews-'.$business->id" />
            </div>
        @endif

    </div>

    @if($business->lat && $business->lng)
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        @endpush

        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const el = document.getElementById('business-map');
                    if (!el) return;

                    const lat  = parseFloat(el.dataset.lat);
                    const lng  = parseFloat(el.dataset.lng);
                    const name = el.dataset.name;

                    const map = L.map('business-map', { zoomControl: true, scrollWheelZoom: false })
                        .setView([lat, lng], 16);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19,
                    }).addTo(map);

                    const icon = L.divIcon({
                        html: `<div style="
                            width:36px;height:36px;border-radius:50% 50% 50% 0;
                            background:#d97706;border:3px solid #fff;
                            transform:rotate(-45deg);
                            box-shadow:0 2px 6px rgba(0,0,0,.3);
                        "></div>`,
                        iconSize: [36, 36],
                        iconAnchor: [18, 36],
                        popupAnchor: [0, -38],
                        className: '',
                    });

                    const popup = document.createElement('strong');
                    popup.textContent = name;

                    L.marker([lat, lng], { icon })
                        .addTo(map)
                        .bindPopup(popup)
                        .openPopup();
                });
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            function businessContactTracking(businessId) {
                const trackUrl = '{{ route("business.track", ["business" => "__ID__", "eventType" => "__TYPE__"]) }}';
                return {
                    trackContact(eventType) {
                        fetch(trackUrl.replace('__ID__', businessId).replace('__TYPE__', eventType), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        }).catch(() => {});
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>

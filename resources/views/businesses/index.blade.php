<x-app-layout>
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    @endpush

    <div
        x-data="{ view: 'list' }"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    >
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Serviços e Comércios</h1>

            <div class="flex items-center gap-3">
                {{-- Toggle lista / mapa --}}
                @if($mapBusinesses->isNotEmpty())
                    <div class="flex items-center bg-stone-100 rounded-lg p-1 gap-1">
                        <button
                            @click="view = 'list'"
                            :class="view === 'list' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-150"
                        >
                            <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                            Lista
                        </button>
                        <button
                            @click="view = 'map'"
                            :class="view === 'map' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-150"
                        >
                            <x-heroicon-o-map class="w-4 h-4" />
                            Mapa
                            <span class="text-xs text-stone-400">({{ $mapBusinesses->count() }})</span>
                        </button>
                    </div>
                @endif

                @auth
                    <a href="{{ route('businesses.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Cadastrar Negócio
                    </a>
                @endauth
            </div>
        </div>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        {{-- Vista: Lista --}}
        <div x-show="view === 'list'" x-transition>
            <livewire:business.business-list />
        </div>

        {{-- Vista: Mapa --}}
        @if($mapBusinesses->isNotEmpty())
            <div
                x-show="view === 'map'"
                x-transition
                x-effect="if (view === 'map') { $nextTick(() => window.dispatchEvent(new Event('map-visible'))) }"
            >
                <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
                    <div id="businesses-map" class="w-full" style="height: 600px; z-index: 0;"></div>
                </div>
                <p class="text-xs text-stone-400 mt-2 text-right">
                    {{ $mapBusinesses->count() }} negócio(s) com localização cadastrada
                </p>
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            const businesses = @json($mapBusinesses);

            document.addEventListener('DOMContentLoaded', function () {
                if (!businesses.length) return;

                const map = L.map('businesses-map', { scrollWheelZoom: true })
                    .setView([businesses[0].lat, businesses[0].lng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    maxZoom: 19,
                }).addTo(map);

                const makeIcon = (featured) => L.divIcon({
                    html: `<div style="
                        width:${featured ? 38 : 30}px;
                        height:${featured ? 38 : 30}px;
                        border-radius:50% 50% 50% 0;
                        background:${featured ? '#d97706' : '#78716c'};
                        border:3px solid #fff;
                        transform:rotate(-45deg);
                        box-shadow:0 2px 6px rgba(0,0,0,.25);
                    "></div>`,
                    iconSize: [featured ? 38 : 30, featured ? 38 : 30],
                    iconAnchor: [featured ? 19 : 15, featured ? 38 : 30],
                    popupAnchor: [0, featured ? -40 : -32],
                    className: '',
                });

                const bounds = [];

                businesses.forEach(b => {
                    const marker = L.marker([b.lat, b.lng], { icon: makeIcon(b.featured) })
                        .addTo(map)
                        .bindPopup(`
                            <div style="min-width:160px">
                                <p style="font-weight:600;font-size:14px;margin:0 0 2px">${b.name}</p>
                                <p style="font-size:12px;color:#78716c;margin:0 0 8px">${b.category ?? ''} · ${b.neighborhood}</p>
                                <a href="${b.url}" style="font-size:12px;color:#d97706;font-weight:500;">Ver perfil →</a>
                            </div>
                        `);
                    bounds.push([b.lat, b.lng]);
                });

                if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }

                window.addEventListener('map-visible', () => map.invalidateSize());
            });
        </script>
    @endpush
</x-app-layout>

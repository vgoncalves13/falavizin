<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Configurações</h1>
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-stone-200 divide-y divide-stone-100">

        {{-- Section: Bairro atendido --}}
        <div class="p-6"
             x-data="neighborhoodSettings(@entangle('neighborhoodLat'), @entangle('neighborhoodLng'))">

            <h2 class="text-base font-semibold text-stone-900 mb-1" style="font-family: var(--font-display)">
                Bairro atendido
            </h2>
            <p class="text-sm text-stone-500 mb-5">
                Define o bairro principal da plataforma. As coordenadas serão usadas como ponto inicial no mapa de importação do Google Places.
            </p>

            <div class="space-y-4">
                {{-- Nome do bairro --}}
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Nome do bairro</label>
                    <input type="text"
                           wire:model="neighborhoodName"
                           placeholder="Ex: Copacabana"
                           class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                    @error('neighborhoodName')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Busca de localização --}}
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Buscar localização</label>
                    <div class="flex gap-2">
                        <input type="text"
                               x-model="searchQuery"
                               x-on:keydown.enter.prevent="geocode()"
                               placeholder="Ex: Copacabana, Rio de Janeiro"
                               class="flex-1 rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        <button type="button"
                                x-on:click="geocode()"
                                :disabled="searching"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-lg transition-colors duration-150 disabled:opacity-60">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" x-show="!searching" />
                            <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" x-show="searching" x-cloak />
                            Buscar
                        </button>
                    </div>
                    <p x-show="geocodeError" x-text="geocodeError" class="mt-1 text-xs text-red-500" x-cloak></p>
                </div>

                {{-- Coordenadas --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Latitude</label>
                        <input type="number" step="any"
                               wire:model.number="neighborhoodLat"
                               x-on:change="updateMarker()"
                               placeholder="-22.9711"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('neighborhoodLat')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Longitude</label>
                        <input type="number" step="any"
                               wire:model.number="neighborhoodLng"
                               x-on:change="updateMarker()"
                               placeholder="-43.1823"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('neighborhoodLng')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Mapa de confirmação --}}
                <div class="rounded-xl overflow-hidden border border-stone-200" wire:ignore>
                    <div id="settings-map" class="w-full h-64"></div>
                    <p class="text-xs text-stone-400 px-3 py-2">
                        Clique no mapa para ajustar o ponto central.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 disabled:opacity-60">
                    <span wire:loading wire:target="save">
                        <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                    </span>
                    <x-heroicon-o-check class="w-4 h-4" wire:loading.remove wire:target="save" />
                    Salvar configurações
                </button>
            </div>
        </div>

    </div>
</div>

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@push('scripts')
<script>
function neighborhoodSettings(lat, lng) {
    return {
        lat,
        lng,
        map: null,
        marker: null,
        searchQuery: '',
        searching: false,
        geocodeError: '',

        init() {
            const initLat = parseFloat(this.lat) || -15.7801;
            const initLng = parseFloat(this.lng) || -47.9292;
            const zoom = this.lat ? 13 : 4;

            this.map = L.map('settings-map').setView([initLat, initLng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            if (this.lat && this.lng) {
                this.marker = L.marker([initLat, initLng], { draggable: true }).addTo(this.map);
                this.marker.on('dragend', (e) => this.applyLatLng(e.target.getLatLng()));
            }

            this.map.on('click', (e) => {
                this.applyLatLng(e.latlng);
            });
        },

        applyLatLng({ lat, lng }) {
            const roundedLat = parseFloat(lat.toFixed(7));
            const roundedLng = parseFloat(lng.toFixed(7));

            this.$wire.set('neighborhoodLat', roundedLat);
            this.$wire.set('neighborhoodLng', roundedLng);

            if (this.marker) {
                this.marker.setLatLng([roundedLat, roundedLng]);
            } else {
                this.marker = L.marker([roundedLat, roundedLng], { draggable: true }).addTo(this.map);
                this.marker.on('dragend', (e) => this.applyLatLng(e.target.getLatLng()));
            }
        },

        updateMarker() {
            const lat = parseFloat(this.$wire.neighborhoodLat);
            const lng = parseFloat(this.$wire.neighborhoodLng);
            if (!lat || !lng) return;
            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                this.marker.on('dragend', (e) => this.applyLatLng(e.target.getLatLng()));
            }
            this.map.setView([lat, lng], 13);
        },

        async geocode() {
            if (!this.searchQuery.trim()) return;
            this.searching = true;
            this.geocodeError = '';

            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(this.searchQuery)}`;
                const res = await fetch(url, { headers: { 'Accept-Language': 'pt-BR' } });
                const data = await res.json();

                if (!data.length) {
                    this.geocodeError = 'Nenhum resultado encontrado. Tente um termo mais específico.';
                    return;
                }

                const { lat, lon } = data[0];
                this.applyLatLng({ lat: parseFloat(lat), lng: parseFloat(lon) });
                this.map.setView([parseFloat(lat), parseFloat(lon)], 14);
            } catch {
                this.geocodeError = 'Erro ao buscar localização. Tente novamente.';
            } finally {
                this.searching = false;
            }
        },
    };
}
</script>
@endpush

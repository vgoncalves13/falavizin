<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Bairros</h1>
        @if(! $showForm)
            <button wire:click="create"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                <x-heroicon-o-plus class="w-4 h-4" />
                Novo bairro
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($showForm)
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-stone-900 mb-4" style="font-family: var(--font-display)">
                {{ $editingId ? 'Editar bairro' : 'Novo bairro' }}
            </h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Nome</label>
                        <input type="text" wire:model="name" placeholder="Ex: Copacabana"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Slug</label>
                        <input type="text" wire:model="slug" placeholder="Deixe vazio para gerar automaticamente"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('slug')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Cidade</label>
                        <input type="text" wire:model="city" placeholder="Ex: Rio de Janeiro"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('city')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">City Slug</label>
                        <input type="text" wire:model="citySlug" placeholder="Deixe vazio para gerar"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('city_slug')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">UF</label>
                        <input type="text" wire:model="stateCode" placeholder="RJ" maxlength="2"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        @error('state_code')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div x-data="neighborhoodMap(@entangle('latitude'), @entangle('longitude'), @js($name))" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Latitude</label>
                            <input type="number" step="any" x-model="latitude" x-on:change="moveMarker()" placeholder="-22.9711"
                                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                            @error('latitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Longitude</label>
                            <input type="number" step="any" x-model="longitude" x-on:change="moveMarker()" placeholder="-43.1823"
                                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                            @error('longitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Ordenação</label>
                            <input type="number" wire:model="sortOrder" min="0"
                                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                            @error('sort_order')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div data-neighborhood-map wire:ignore class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50">
                        <form x-on:submit.prevent="searchPlace()" class="flex gap-2 border-b border-stone-200 bg-white p-3">
                            <label for="neighborhood-map-search" class="sr-only">Buscar bairro no mapa</label>
                            <div class="relative flex-1">
                                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
                                <input
                                    id="neighborhood-map-search"
                                    type="search"
                                    x-model="searchQuery"
                                    placeholder="Buscar bairro no mapa"
                                    class="w-full rounded-lg border border-stone-300 py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500"
                                />
                            </div>
                            <button
                                type="submit"
                                :disabled="searching"
                                class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-700 disabled:opacity-60"
                            >
                                <span x-show="!searching">Buscar</span>
                                <span x-show="searching">Buscando...</span>
                            </button>
                        </form>
                        <p
                            x-show="searchMessage"
                            x-text="searchMessage"
                            :class="searchFailed ? 'text-red-600' : 'text-stone-500'"
                            class="border-b border-stone-200 bg-white px-3 py-2 text-xs"
                        ></p>
                        <div x-ref="map" class="relative z-0 h-72 w-full" role="application" aria-label="Mapa para selecionar as coordenadas do bairro"></div>
                        <p class="border-t border-stone-200 px-3 py-2 text-xs text-stone-500">
                            Clique no mapa ou arraste o marcador para definir latitude e longitude.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="isActive" id="is-active"
                           class="rounded border-stone-300 text-amber-600 focus:ring-amber-500" />
                    <label for="is-active" class="text-sm font-medium text-stone-700">Ativo</label>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 disabled:opacity-60">
                    <span wire:loading wire:target="save">
                        <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                    </span>
                    <x-heroicon-o-check class="w-4 h-4" wire:loading.remove wire:target="save" />
                    {{ $editingId ? 'Atualizar' : 'Criar' }}
                </button>
                <button wire:click="cancel"
                        class="px-5 py-2 text-stone-600 hover:text-stone-900 text-sm font-medium rounded-lg hover:bg-stone-100 transition-colors duration-150">
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-stone-200 divide-y divide-stone-100">
        @forelse($neighborhoods as $neighborhood)
            <div class="flex items-center justify-between p-4 gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-stone-900 truncate">{{ $neighborhood->name }}</h3>
                        @if(! $neighborhood->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-stone-100 text-stone-500 rounded-full">Inativo</span>
                        @endif
                    </div>
                    <p class="text-xs text-stone-500 mt-0.5">
                        {{ $neighborhood->city }} - {{ $neighborhood->state_code }} &middot; {{ $neighborhood->slug }}
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button wire:click="$dispatch('edit', { id: {{ $neighborhood->id }} })"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors duration-150">
                        <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                        Editar
                    </button>
                    <button wire:click="toggleActive({{ $neighborhood->id }})"
                            wire:loading.attr="disabled"
                            wire:target="toggleActive"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors duration-150 {{ $neighborhood->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-green-700 hover:bg-green-50' }}">
                        @if($neighborhood->is_active)
                            <x-heroicon-o-eye-slash class="w-3.5 h-3.5" />
                            Desativar
                        @else
                            <x-heroicon-o-eye class="w-3.5 h-3.5" />
                            Ativar
                        @endif
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-heroicon-o-map-pin class="w-10 h-10 text-stone-300 mx-auto mb-3" />
                <p class="text-sm text-stone-500">Nenhum bairro cadastrado.</p>
            </div>
        @endforelse
    </div>
</div>

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@push('scripts')
<script>
function neighborhoodMap(latitude, longitude, initialSearch) {
    return {
        map: null,
        marker: null,
        latitude,
        longitude,
        searchQuery: initialSearch,
        searchMessage: '',
        searchFailed: false,
        searching: false,

        init() {
            const lat = Number.parseFloat(this.latitude);
            const lng = Number.parseFloat(this.longitude);
            const hasCoordinates = Number.isFinite(lat) && Number.isFinite(lng);
            const center = hasCoordinates ? [lat, lng] : [-22.9068, -43.1729];

            this.map = L.map(this.$refs.map).setView(center, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
            }).addTo(this.map);

            if (hasCoordinates) {
                this.addMarker(lat, lng);
            }

            this.map.on('click', (event) => {
                this.setPoint(event.latlng.lat, event.latlng.lng);
            });
        },

        addMarker(lat, lng) {
            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', (event) => {
                const point = event.target.getLatLng();
                this.setPoint(point.lat, point.lng);
            });
        },

        setPoint(lat, lng) {
            this.latitude = lat.toFixed(7);
            this.longitude = lng.toFixed(7);
            this.marker ? this.marker.setLatLng([lat, lng]) : this.addMarker(lat, lng);
        },

        moveMarker() {
            const lat = Number.parseFloat(this.latitude);
            const lng = Number.parseFloat(this.longitude);

            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                this.marker ? this.marker.setLatLng([lat, lng]) : this.addMarker(lat, lng);
                this.map.panTo([lat, lng]);
            }
        },

        async searchPlace() {
            const query = this.searchQuery.trim();

            if (!query) {
                return;
            }

            this.searching = true;
            this.searchMessage = '';
            this.searchFailed = false;

            try {
                const params = new URLSearchParams({
                    format: 'jsonv2',
                    limit: '1',
                    countrycodes: 'br',
                    q: query,
                });
                const response = await fetch(`https://nominatim.openstreetmap.org/search?${params}`);

                if (!response.ok) {
                    throw new Error('Search failed');
                }

                const [place] = await response.json();

                if (!place) {
                    this.searchFailed = true;
                    this.searchMessage = 'Bairro não encontrado.';

                    return;
                }

                const lat = Number.parseFloat(place.lat);
                const lng = Number.parseFloat(place.lon);

                this.setPoint(lat, lng);
                this.map.setView([lat, lng], 15);
                this.searchMessage = place.display_name;
            } catch {
                this.searchFailed = true;
                this.searchMessage = 'Não foi possível buscar agora. Tente novamente.';
            } finally {
                this.searching = false;
            }
        },

        destroy() {
            this.map?.remove();
        },
    };
}
</script>
@endpush

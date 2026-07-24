<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">
            Importar via Google Places
        </h1>
        <a href="{{ route('admin.moderation.index') }}"
           class="text-sm text-stone-500 hover:text-stone-700 flex items-center gap-1">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Moderação
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8"
         x-data="googlePlacesMap(@entangle('lat'), @entangle('lng'), @entangle('radius'))">
        {{-- Config panel --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-stone-700 uppercase tracking-wide">Configuração da Busca</h2>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Bairro</label>
                <select wire:model.live="neighborhoodId"
                        class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="0">Selecione um bairro</option>
                    @foreach($neighborhoods as $neighborhood)
                        <option value="{{ $neighborhood->id }}">{{ $neighborhood->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">
                    Categoria
                    <span class="text-stone-400 font-normal">(fallback)</span>
                </label>
                <select wire:model.number="categoryId"
                        class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="0">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">
                    Raio: <span x-text="$wire.radius"></span> m
                </label>
                <input type="range"
                       wire:model.number="radius"
                       x-on:input="updateCircle()"
                       min="100" max="5000" step="100"
                       class="w-full accent-amber-600" />
                <div class="flex justify-between text-xs text-stone-400 mt-0.5">
                    <span>100m</span><span>5km</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Latitude</label>
                <input type="number" step="any"
                       wire:model.number="lat"
                       x-on:change="moveMarker()"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Longitude</label>
                <input type="number" step="any"
                       wire:model.number="lng"
                       x-on:change="moveMarker()"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">
                    Máximo de resultados
                    <span class="text-stone-400 font-normal">(limite da API: 20)</span>
                </label>
                <input type="number"
                       wire:model.number="maxResults"
                       min="1" max="20"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <button wire:click="search"
                    wire:loading.attr="disabled"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors duration-150 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span wire:loading wire:target="search">
                    <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                </span>
                <x-heroicon-o-magnifying-glass class="w-4 h-4" wire:loading.remove wire:target="search" />
                Buscar negócios
            </button>
        </div>

        {{-- Map --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-stone-200 overflow-hidden" wire:ignore>
            <div id="places-map" class="relative z-0 w-full h-80 lg:h-full min-h-72"></div>
            <p class="text-xs text-stone-400 px-4 py-2">
                Clique no mapa para definir o centro da busca.
            </p>
        </div>
    </div>

    {{-- Results --}}
    @if($searched)
        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-stone-700">
                    Resultados
                    <span class="ml-1 text-stone-400 font-normal">({{ count($results) }} encontrados)</span>
                </h2>
                <div class="flex items-center gap-3">
                    <button wire:click="selectAll"
                            class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                        Selecionar disponíveis
                    </button>
                    @if(count($selected) > 0)
                        <button wire:click="import"
                                wire:loading.attr="disabled"
                                wire:target="import"
                                class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors duration-150 disabled:opacity-60 flex items-center gap-1.5">
                            <span wire:loading wire:target="import">
                                <x-heroicon-o-arrow-path class="w-3.5 h-3.5 animate-spin" />
                            </span>
                            Importar {{ count($selected) }} selecionado(s)
                        </button>
                    @endif
                </div>
            </div>

            @if(count($results) === 0)
                <div class="px-5 py-10 text-center text-stone-400 text-sm">
                    Nenhum resultado encontrado para essa área.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-stone-100 text-xs text-stone-400 uppercase tracking-wide">
                                <th class="px-5 py-3 text-left w-8"></th>
                                <th class="px-5 py-3 text-left">Nome</th>
                                <th class="px-5 py-3 text-left">Tipo</th>
                                <th class="px-5 py-3 text-left">Endereço</th>
                                <th class="px-5 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-50">
                            @foreach($results as $place)
                                @php
                                    $genericTypes = ['establishment', 'point_of_interest', 'food', 'store'];
                                    $primaryType = collect($place['types'])->first(fn($t) => ! in_array($t, $genericTypes)) ?? ($place['types'][0] ?? null);
                                @endphp
                                <tr class="{{ $place['already_imported'] ? 'opacity-50' : (! $place['is_likely_business'] ? 'bg-red-50/50' : 'hover:bg-stone-50') }} transition-colors">
                                    <td class="px-5 py-3">
                                        @if(! $place['already_imported'])
                                            <input type="checkbox"
                                                   wire:model="selected"
                                                   value="{{ $place['place_id'] }}"
                                                   class="rounded border-stone-300 accent-amber-600" />
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-medium text-stone-900">
                                        {{ $place['name'] }}
                                        @if(! empty($place['website']))
                                            <a href="{{ $place['website'] }}" target="_blank"
                                               class="ml-1 text-stone-400 hover:text-amber-600">
                                                <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5 inline" />
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if(! $place['is_likely_business'])
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full">
                                                <x-heroicon-o-exclamation-triangle class="w-3 h-3" />
                                                Não comercial
                                            </span>
                                        @elseif($primaryType)
                                            <span class="text-xs text-stone-400">{{ str_replace('_', ' ', $primaryType) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $place['address'] ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if($place['already_imported'])
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-stone-400 bg-stone-100 px-2 py-0.5 rounded-full">
                                                <x-heroicon-o-check class="w-3 h-3" /> Já importado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                                                Disponível
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($nextPageToken)
                <div class="px-5 py-4 border-t border-stone-100 text-center">
                    <button wire:click="loadMore"
                            wire:loading.attr="disabled"
                            wire:target="loadMore"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-lg transition-colors duration-150 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading wire:target="loadMore">
                            <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                        </span>
                        <x-heroicon-o-arrow-down class="w-4 h-4" wire:loading.remove wire:target="loadMore" />
                        Carregar mais negócios
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@push('scripts')
<script>
function googlePlacesMap(lat, lng, radius) {
    return {
        map: null,
        marker: null,
        circle: null,
        lat,
        lng,
        radius,

        init() {
            this.map = L.map('places-map').setView([this.lat, this.lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
            this.circle = L.circle([this.lat, this.lng], { radius: this.radius, color: '#d97706', fillOpacity: 0.1 }).addTo(this.map);

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.$wire.set('lat', parseFloat(pos.lat.toFixed(7)));
                this.$wire.set('lng', parseFloat(pos.lng.toFixed(7)));
                this.circle.setLatLng([pos.lat, pos.lng]);
            });

            this.map.on('click', (e) => {
                const { lat, lng } = e.latlng;
                this.$wire.set('lat', parseFloat(lat.toFixed(7)));
                this.$wire.set('lng', parseFloat(lng.toFixed(7)));
                this.marker.setLatLng([lat, lng]);
                this.circle.setLatLng([lat, lng]);
            });

            this.$watch('lat', () => this.moveMarker());
            this.$watch('lng', () => this.moveMarker());
        },

        updateCircle() {
            if (this.circle) {
                this.circle.setRadius(this.$wire.radius);
            }
        },

        moveMarker() {
            const lat = this.$wire.lat;
            const lng = this.$wire.lng;
            if (this.marker && lat && lng) {
                this.marker.setLatLng([lat, lng]);
                this.circle.setLatLng([lat, lng]);
                this.map.setView([lat, lng]);
            }
        },
    };
}
</script>
@endpush

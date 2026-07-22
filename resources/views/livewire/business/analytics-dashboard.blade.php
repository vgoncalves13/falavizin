<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-stone-700 flex items-center gap-2">
            <x-heroicon-o-chart-bar class="w-4 h-4 text-amber-500" />
            Métricas do seu negócio
        </h3>
        <div class="flex gap-1 bg-stone-100 rounded-lg p-0.5">
            <button
                wire:click="setDays(7)"
                class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors {{ $days === 7 ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700' }}"
            >7 dias</button>
            <button
                wire:click="setDays(30)"
                class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors {{ $days === 30 ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700' }}"
            >30 dias</button>
            <button
                wire:click="setDays(90)"
                class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors {{ $days === 90 ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700' }}"
            >90 dias</button>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-stone-50 rounded-lg p-3 text-center border border-stone-200">
            <div class="flex items-center justify-center gap-1.5 mb-1">
                <x-heroicon-o-eye class="w-4 h-4 text-stone-400" />
                <span class="text-2xl font-bold text-stone-800">{{ $stats['views'] ?? 0 }}</span>
            </div>
            <p class="text-xs text-stone-500">Visualizações</p>
        </div>
        <div class="bg-stone-50 rounded-lg p-3 text-center border border-stone-200">
            <div class="flex items-center justify-center gap-1.5 mb-1">
                <x-heroicon-o-phone class="w-4 h-4 text-stone-400" />
                <span class="text-2xl font-bold text-stone-800">{{ $stats['phone_clicks'] ?? 0 }}</span>
            </div>
            <p class="text-xs text-stone-500">Ligações</p>
        </div>
        <div class="bg-green-50 rounded-lg p-3 text-center border border-green-200">
            <div class="flex items-center justify-center gap-1.5 mb-1">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span class="text-2xl font-bold text-green-700">{{ $stats['whatsapp_clicks'] ?? 0 }}</span>
            </div>
            <p class="text-xs text-green-600">WhatsApp</p>
        </div>
    </div>

    @if(!empty($dailyStats))
        <div class="bg-stone-50 rounded-lg p-3 border border-stone-200">
            <p class="text-xs font-medium text-stone-600 mb-2">Atividade diária</p>
            <div class="flex items-end gap-1 h-16">
                @php
                    $maxDaily = collect($dailyStats)->flatMap(fn($day) => collect($day)->values())->max() ?: 1;
                @endphp
                @foreach($dailyStats as $date => $day)
                    @php
                        $total = collect($day)->sum();
                        $height = max(4, round(($total / $maxDaily) * 100));
                    @endphp
                    <div
                        class="flex-1 bg-amber-400 rounded-t"
                        style="height: {{ $height }}%"
                        title="{{ \Carbon\Carbon::parse($date)->format('d/m') }}: {{ $total }} eventos"
                    ></div>
                @endforeach
            </div>
            <div class="flex justify-between mt-1">
                <span class="text-[10px] text-stone-400">{{ \Carbon\Carbon::parse(array_key_first($dailyStats))->format('d/m') }}</span>
                <span class="text-[10px] text-stone-400">{{ \Carbon\Carbon::parse(array_key_last($dailyStats))->format('d/m') }}</span>
            </div>
        </div>
    @else
        <p class="text-xs text-stone-400 text-center py-3">Nenhum dado ainda. As métricas aparecem quando alguém visita seu negócio.</p>
    @endif
</div>

@props([
    'images' => [],
    'title' => '',
    'detail' => false,
])

@php
    $paths = collect($images)->filter()->take(4)->values();
    $urls = $paths->map(fn (string $path) => Storage::url($path))->all();
    $count = count($urls);
@endphp

@if($count > 0)
    <div
        @if($detail)
            x-data="{ active: null, photos: @js($urls) }"
            x-on:keydown.escape.window="active = null"
        @endif
        class="bg-stone-100"
    >
        @if($count === 1)
            @if($detail)
                <button
                    type="button"
                    x-on:click="active = 0"
                    class="flex max-h-[70vh] w-full items-center justify-center overflow-hidden bg-stone-100"
                    aria-label="Ampliar foto"
                >
                    <img
                        src="{{ $urls[0] }}"
                        alt="{{ $title }}"
                        class="block max-h-[70vh] w-full object-contain"
                    />
                </button>
            @else
                <div class="flex aspect-[4/3] items-center justify-center overflow-hidden bg-stone-100">
                    <img
                        src="{{ $urls[0] }}"
                        alt="{{ $title }}"
                        class="block h-full w-full object-contain"
                    />
                </div>
            @endif
        @else
            <div class="grid aspect-[4/3] grid-cols-2 grid-rows-2 gap-0.5 overflow-hidden bg-stone-200">
                @foreach($urls as $index => $url)
                    @php
                        $span = match ($count) {
                            2 => 'row-span-2',
                            3 => $index === 0 ? 'row-span-2' : '',
                            default => '',
                        };
                    @endphp

                    @if($detail)
                        <button
                            type="button"
                            x-on:click="active = {{ $index }}"
                            class="{{ $span }} min-h-0 overflow-hidden bg-stone-100"
                            aria-label="Ampliar foto {{ $index + 1 }}"
                        >
                            <img
                                src="{{ $url }}"
                                alt="{{ $title }} — foto {{ $index + 1 }}"
                                class="block h-full w-full object-cover transition-transform duration-200 hover:scale-[1.02]"
                            />
                        </button>
                    @else
                        <div class="{{ $span }} min-h-0 overflow-hidden bg-stone-100">
                            <img
                                src="{{ $url }}"
                                alt="{{ $title }} — foto {{ $index + 1 }}"
                                class="block h-full w-full object-cover"
                            />
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if($detail)
            <template x-teleport="body">
                <div
                    x-cloak
                    x-show="active !== null"
                    x-transition.opacity
                    x-on:click.self="active = null"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/95 p-4 sm:p-8"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Visualização das fotos"
                >
                    <button
                        type="button"
                        x-on:click="active = null"
                        class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20"
                        aria-label="Fechar"
                    >
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>

                    <button
                        x-show="photos.length > 1"
                        type="button"
                        x-on:click="active = (active - 1 + photos.length) % photos.length"
                        class="absolute left-3 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20 sm:left-6"
                        aria-label="Foto anterior"
                    >
                        <x-heroicon-o-chevron-left class="h-7 w-7" />
                    </button>

                    <img
                        x-bind:src="photos[active]"
                        alt="{{ $title }}"
                        class="max-h-full max-w-full object-contain"
                    />

                    <button
                        x-show="photos.length > 1"
                        type="button"
                        x-on:click="active = (active + 1) % photos.length"
                        class="absolute right-3 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20 sm:right-6"
                        aria-label="Próxima foto"
                    >
                        <x-heroicon-o-chevron-right class="h-7 w-7" />
                    </button>

                    <span
                        x-show="photos.length > 1"
                        class="absolute bottom-4 rounded-full bg-black/40 px-3 py-1 text-xs font-medium text-white"
                        x-text="`${active + 1} / ${photos.length}`"
                    ></span>
                </div>
            </template>
        @endif
    </div>
@endif

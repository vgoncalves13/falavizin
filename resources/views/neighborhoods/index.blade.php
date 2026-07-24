<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">
                Escolha seu bairro
            </h1>
            <p class="mt-2 text-lg text-stone-500">
                Selecione o bairro para ver os conteúdos e serviços da sua região.
            </p>
        </div>

        @if ($lastNeighborhood)
            <div class="mt-8 flex justify-center">
                <a href="{{ route('neighborhood.home', $lastNeighborhood->routeParameters()) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                    Continuar em {{ $lastNeighborhood->name }}
                </a>
            </div>
        @endif

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($neighborhoods as $neighborhood)
                <a href="{{ route('neighborhood.home', $neighborhood->routeParameters()) }}"
                   class="group flex items-start gap-4 rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-amber-300 hover:shadow-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                        <x-heroicon-o-map-pin class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-stone-900 group-hover:text-amber-700">
                            {{ $neighborhood->name }}
                        </h2>
                        <p class="mt-0.5 text-sm text-stone-500">
                            {{ $neighborhood->city }}/{{ $neighborhood->state_code }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($neighborhoods->isEmpty())
            <div class="mt-10 text-center">
                <x-heroicon-o-map-pin class="mx-auto h-12 w-12 text-stone-300" />
                <p class="mt-3 text-stone-500">Nenhum bairro disponível no momento.</p>
            </div>
        @endif
    </div>
</x-app-layout>

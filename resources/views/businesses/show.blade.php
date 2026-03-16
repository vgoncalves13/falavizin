<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('businesses.index') }}" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700 mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Voltar aos Serviços
        </a>

        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h1 class="text-2xl font-bold text-stone-900 mb-2" style="font-family: var(--font-display)">
                {{ $business->name }}
            </h1>
            @if($business->description)
                <p class="text-stone-600">{{ $business->description }}</p>
            @endif
        </div>
    </div>
</x-app-layout>

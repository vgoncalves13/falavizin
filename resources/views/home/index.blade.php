<x-app-layout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-amber-50 to-stone-100 border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <div class="max-w-2xl">
                <h1 class="text-4xl lg:text-5xl font-bold text-stone-900 leading-tight mb-4" style="font-family: var(--font-display)">
                    O que está acontecendo<br>
                    <span class="text-amber-600">no seu bairro?</span>
                </h1>
                <p class="text-lg text-stone-600 mb-8">
                    Serviços, eventos, avisos e muito mais — tudo do seu bairro, em um só lugar.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('feed.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-xl transition-colors duration-150">
                        <x-heroicon-o-newspaper class="w-5 h-5" />
                        Ver o Feed
                    </a>
                    <a href="{{ route('businesses.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-stone-50 text-stone-700 font-medium rounded-xl border border-stone-200 transition-colors duration-150">
                        <x-heroicon-o-building-storefront class="w-5 h-5" />
                        Encontrar Serviços
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-xl font-bold text-stone-900 mb-6" style="font-family: var(--font-display)">Categorias</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            {{-- Populadas na Semana 4 com Cache::remember() --}}
            <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150 cursor-pointer">
                <x-heroicon-o-bolt class="w-6 h-6 text-amber-600" />
                <span class="text-xs font-medium text-stone-700 text-center">Elétrica</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150 cursor-pointer">
                <x-heroicon-o-wrench class="w-6 h-6 text-amber-600" />
                <span class="text-xs font-medium text-stone-700 text-center">Encanamento</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150 cursor-pointer">
                <x-heroicon-o-cake class="w-6 h-6 text-amber-600" />
                <span class="text-xs font-medium text-stone-700 text-center">Alimentação</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150 cursor-pointer">
                <x-heroicon-o-heart class="w-6 h-6 text-amber-600" />
                <span class="text-xs font-medium text-stone-700 text-center">Saúde</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150 cursor-pointer">
                <x-heroicon-o-academic-cap class="w-6 h-6 text-amber-600" />
                <span class="text-xs font-medium text-stone-700 text-center">Educação</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150 cursor-pointer">
                <x-heroicon-o-sparkles class="w-6 h-6 text-amber-600" />
                <span class="text-xs font-medium text-stone-700 text-center">Beleza</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-colors duration-150 cursor-pointer">
                <x-heroicon-o-megaphone class="w-6 h-6 text-amber-600" />
                <span class="text-xs font-medium text-stone-700 text-center">Avisos</span>
            </div>
        </div>
    </section>

    <!-- Feed Recente e Negócios em Destaque -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16">
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Feed Recente -->
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">Últimas do Bairro</h2>
                    <a href="{{ route('feed.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700">
                        Ver tudo →
                    </a>
                </div>
                <div class="space-y-3">
                    <div class="bg-white rounded-xl border border-stone-200 p-5">
                        <p class="text-sm text-stone-500">O feed será exibido aqui com os últimos posts aprovados do bairro.</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar: Negócios em Destaque -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-stone-900" style="font-family: var(--font-display)">Em Destaque</h2>
                    <a href="{{ route('businesses.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700">
                        Ver tudo →
                    </a>
                </div>
                <div class="space-y-3">
                    <div class="bg-amber-50 rounded-xl border border-amber-200 p-4">
                        <p class="text-sm text-stone-500">Negócios com plano destaque aparecerão aqui.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>

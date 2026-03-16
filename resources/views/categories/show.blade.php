<x-app-layout :title="$category->name">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-3 mb-6">
            <x-dynamic-component :component="'heroicon-o-' . $category->icon" class="w-6 h-6 text-amber-600" />
            <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">
                {{ $category->name }}
            </h1>
        </div>

        {{-- Posts --}}
        @if($posts !== null)
            <div class="mb-10">
                @if($posts->isEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                        <x-heroicon-o-newspaper class="w-8 h-8 text-stone-300 mx-auto mb-2" />
                        <p class="text-stone-500 text-sm">Nenhum post nesta categoria ainda.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($posts as $post)
                            <x-post-card :post="$post" />
                        @endforeach
                    </div>
                    @if($posts->hasPages())
                        <div class="mt-6">{{ $posts->links() }}</div>
                    @endif
                @endif
            </div>
        @endif

        {{-- Negócios --}}
        @if($businesses !== null)
            @if($businesses->isEmpty())
                <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                    <x-heroicon-o-building-storefront class="w-8 h-8 text-stone-300 mx-auto mb-2" />
                    <p class="text-stone-500 text-sm">Nenhum negócio nesta categoria ainda.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($businesses as $business)
                        <x-business-card :business="$business" />
                    @endforeach
                </div>
                @if($businesses->hasPages())
                    <div class="mt-6">{{ $businesses->links() }}</div>
                @endif
            @endif
        @endif
    </div>
</x-app-layout>

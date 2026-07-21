@props(['business'])

<a
    href="{{ route('businesses.show', $business) }}"
    class="group block bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-md transition-shadow duration-200 {{ $business->plan->value === 'featured' ? 'border-amber-300 ring-1 ring-amber-200' : '' }}"
>
    {{-- Cover photo --}}
    <div class="aspect-video bg-stone-100 overflow-hidden relative">
        @if($business->coverPhoto)
            <img
                src="{{ Storage::url($business->coverPhoto->path) }}"
                alt="{{ $business->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
        @else
            <div class="w-full h-full flex items-center justify-center">
                <x-dynamic-component
                    :component="'heroicon-o-' . ($business->category->icon ?? 'building-storefront')"
                    class="w-12 h-12 text-stone-300"
                />
            </div>
        @endif

        @if($business->plan->value === 'featured')
            <span class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white shadow-sm">
                <x-heroicon-s-star class="w-3 h-3" />
                Destaque
            </span>
        @endif
    </div>

    {{-- Info --}}
    <div class="p-4">
        <div class="flex items-start justify-between gap-2 mb-1">
            <h3 class="font-semibold text-stone-900 leading-snug group-hover:text-amber-700 transition-colors">
                {{ $business->name }}
            </h3>
        </div>

        <div class="flex items-center justify-between gap-2 mt-1">
            <div class="flex flex-wrap gap-1">
                @foreach($business->categories->take(3) as $category)
                    <x-category-badge :category="$category" />
                @endforeach
                @if($business->categories->count() > 3)
                    <span class="inline-flex items-center text-xs text-stone-500 px-1">+{{ $business->categories->count() - 3 }}</span>
                @endif
            </div>
            @php
                $avg = $business->averageRating();
                $positiveReviews = $business->positiveReviewsCount();
            @endphp
            <div class="flex items-center gap-1.5 shrink-0">
                @if($positiveReviews >= 3)
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded-full">
                        <x-heroicon-s-hand-thumb-up class="w-3 h-3" />
                        {{ $positiveReviews }}
                    </span>
                @endif
                @if($avg)
                    <div class="flex items-center gap-1">
                        <x-heroicon-s-star class="w-3.5 h-3.5 text-amber-400" />
                        <span class="text-xs font-medium text-stone-600">{{ number_format($avg, 1, ',') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-2 flex items-center justify-between gap-2">
            <div class="flex items-center gap-1 text-xs text-stone-500 min-w-0">
                <x-heroicon-o-map-pin class="w-3.5 h-3.5 shrink-0" />
                <span class="truncate">{{ $business->neighborhood }}{{ $business->city ? ', ' . $business->city : '' }}</span>
            </div>
            @php $openNow = $business->isOpenNow(); @endphp
            @if($openNow === true)
                <span class="shrink-0 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Aberto
                </span>
            @elseif($openNow === false)
                <span class="shrink-0 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-stone-50 text-stone-400 border border-stone-200">
                    Fechado
                </span>
            @endif
        </div>

        @if($business->description)
            <p class="mt-2 text-sm text-stone-600 line-clamp-2">{{ $business->description }}</p>
        @endif
    </div>
</a>

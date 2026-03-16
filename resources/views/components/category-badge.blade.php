@props(['category'])

<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
    <x-dynamic-component :component="'heroicon-o-' . $category->icon" class="w-3 h-3" />
    {{ $category->name }}
</span>

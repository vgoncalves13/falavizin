<div class="bg-white rounded-xl border {{ isset($reported) && $reported ? 'border-red-200' : 'border-stone-200' }} p-4 flex items-start justify-between gap-4">
    <div class="min-w-0">
        <p class="font-medium text-stone-900 truncate">{{ $title }}</p>
        <p class="text-xs text-stone-400 mt-0.5">{{ $meta }}</p>
        @if($excerpt)
            <p class="text-sm text-stone-600 mt-1 line-clamp-2">{{ $excerpt }}</p>
        @endif
        @if(isset($model->reported_reason) && $model->reported_reason)
            <p class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                <x-heroicon-o-flag class="w-3 h-3" />
                {{ $model->reported_reason }}
            </p>
        @endif
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <form action="{{ route('admin.moderation.approve', ['type' => $type, 'id' => $model->id]) }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors">
                Aprovar
            </button>
        </form>
        <form action="{{ route('admin.moderation.reject', ['type' => $type, 'id' => $model->id]) }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-colors">
                Rejeitar
            </button>
        </form>
    </div>
</div>

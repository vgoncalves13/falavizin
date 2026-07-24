<div class="bg-white rounded-xl border {{ isset($reported) && $reported ? 'border-red-200' : 'border-stone-200' }} p-4 flex items-start gap-3">
    @if(isset($selectable) && $selectable)
        <div class="pt-0.5">
            <input
                type="checkbox"
                :value="{{ $model->id }}"
                x-model="selected"
                class="w-4 h-4 rounded border-stone-300 text-amber-600 focus:ring-amber-500 cursor-pointer"
            />
        </div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="font-medium text-stone-900 truncate">{{ $title }}</p>
        <p class="text-xs text-stone-400 mt-0.5">{{ $meta }}</p>
        @if($excerpt)
            @if($type === 'post')
                <div x-data="{ expanded: false }">
                    <p class="text-sm text-stone-600 mt-1" :class="expanded ? '' : 'line-clamp-2'">{{ $excerpt }}</p>
                    <button
                        @click="expanded = !expanded"
                        class="mt-1 text-xs text-amber-600 hover:text-amber-700 font-medium"
                        x-text="expanded ? 'Ver menos' : 'Ver completo'"
                    ></button>
                </div>
            @else
                <p class="text-sm text-stone-600 mt-1 line-clamp-2">{{ $excerpt }}</p>
            @endif
        @endif
        @if($type === 'post' && isset($model->status) && $model->status->value === 'approved')
            <a href="{{ $model->canonicalUrl() }}" target="_blank"
               class="mt-1.5 inline-flex items-center gap-1 text-xs text-stone-400 hover:text-amber-600 transition-colors">
                <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
                Abrir post
            </a>
        @endif
        @if(isset($model->reported_reason) && $model->reported_reason)
            <p class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                <x-heroicon-o-flag class="w-3 h-3" />
                {{ $model->reported_reason }}
            </p>
        @endif
    </div>
    <div class="flex items-center gap-2 shrink-0">
        @if($type === 'post' && isset($model->status) && $model->status->value === 'approved')
            <form action="{{ route('admin.posts.sponsor', $model) }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-1.5 {{ $model->is_sponsored ? 'bg-amber-500 hover:bg-amber-600' : 'bg-stone-200 hover:bg-amber-100 text-stone-600' }} text-white text-xs font-medium rounded-lg transition-colors">
                    {{ $model->is_sponsored ? 'Patrocinado' : 'Patrocinar' }}
                </button>
            </form>
        @endif
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

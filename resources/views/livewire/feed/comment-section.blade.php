<div>
    <h2 class="text-lg font-semibold text-stone-900 mb-4">
        Comentários <span class="text-stone-400 font-normal text-base">({{ $comments->count() }})</span>
    </h2>

    {{-- Formulário de novo comentário --}}
    @auth
        <form wire:submit="addComment" class="mb-6">
            <div class="flex gap-3">
                <div class="shrink-0 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                    <span class="text-xs font-bold text-amber-700">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div class="flex-1">
                    <textarea
                        wire:model="body"
                        rows="2"
                        placeholder="Escreva um comentário..."
                        class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 resize-none"
                    ></textarea>
                    @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <div class="mt-2 flex justify-end">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-sm font-medium rounded-lg transition-colors duration-150"
                        >
                            <span wire:loading.remove>Comentar</span>
                            <span wire:loading>Enviando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="mb-6 p-4 bg-stone-50 rounded-lg border border-stone-200 text-center">
            <p class="text-sm text-stone-500">
                <a href="{{ route('login') }}" class="text-amber-600 font-medium hover:text-amber-700">Entre</a>
                para comentar.
            </p>
        </div>
    @endauth

    {{-- Lista de comentários --}}
    <div class="space-y-4">
        @forelse($comments as $comment)
            <div class="flex gap-3">
                <div class="shrink-0 w-8 h-8 rounded-full bg-stone-200 flex items-center justify-center">
                    <span class="text-xs font-bold text-stone-600">{{ substr($comment->user->name, 0, 1) }}</span>
                </div>
                <div class="flex-1">
                    <div class="bg-stone-50 rounded-lg px-4 py-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-stone-900">{{ $comment->user->name }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-stone-400">{{ $comment->created_at->diffForHumans() }}</span>
                                @if(auth()->id() === $comment->user_id || auth()->user()?->is_admin)
                                    <button
                                        wire:click="deleteComment({{ $comment->id }})"
                                        wire:confirm="Remover este comentário?"
                                        class="text-stone-400 hover:text-red-500 transition-colors"
                                    >
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </button>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-stone-700">{{ $comment->body }}</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-stone-400 text-center py-4">Seja o primeiro a comentar.</p>
        @endforelse
    </div>
</div>

<div>
    <h2 class="text-lg font-semibold text-stone-900 mb-4">
        Comentários <span class="text-stone-400 font-normal text-base">({{ $comments->sum(fn($c) => 1 + $c->replies->count()) }})</span>
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
                    {{-- Balão do comentário --}}
                    <div class="bg-stone-50 rounded-lg px-4 py-3">
                        <div class="flex items-center justify-between mb-1">
                            <a href="{{ route('users.show', $comment->user) }}"
                               class="text-sm font-medium text-stone-900 hover:text-amber-700 transition-colors">{{ $comment->user->name }}</a>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-stone-400">{{ $comment->created_at->diffForHumans() }}</span>
                                @can('update', $comment)
                                    <button wire:click="startEdit({{ $comment->id }})"
                                            class="text-stone-400 hover:text-amber-600 transition-colors" title="Editar comentário">
                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                    </button>
                                @endcan
                                @can('delete', $comment)
                                    <button wire:click="deleteComment({{ $comment->id }})"
                                            wire:confirm="Remover este comentário?"
                                            class="text-stone-400 hover:text-red-500 transition-colors" title="Excluir comentário">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </button>
                                @endcan
                            </div>
                        </div>

                        @if($editingId === $comment->id)
                            <textarea wire:model="editBody" rows="2"
                                      class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 resize-none mt-1"></textarea>
                            @error('editBody') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            <div class="mt-2 flex gap-2 justify-end">
                                <button wire:click="cancelEdit" type="button"
                                        class="px-3 py-1 text-xs font-medium text-stone-600 hover:text-stone-800 bg-white border border-stone-300 rounded-lg transition-colors duration-150">
                                    Cancelar
                                </button>
                                <button wire:click="saveEdit" wire:loading.attr="disabled" type="button"
                                        class="px-3 py-1 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-xs font-medium rounded-lg transition-colors duration-150">
                                    <span wire:loading.remove wire:target="saveEdit">Salvar</span>
                                    <span wire:loading wire:target="saveEdit">Salvando...</span>
                                </button>
                            </div>
                        @else
                            <p class="text-sm text-stone-700">{{ $comment->body }}</p>
                        @endif
                    </div>

                    {{-- Ações: voto + responder --}}
                    <div class="mt-1.5 ml-1 flex items-center gap-3">
                        @php $commentVoted = in_array($comment->id, $userVotedIds); @endphp
                        <button
                            wire:click="voteComment({{ $comment->id }})"
                            class="inline-flex items-center gap-1 text-xs transition-colors {{ $commentVoted ? 'text-amber-600' : 'text-stone-400 hover:text-amber-600' }}"
                            title="{{ $commentVoted ? 'Remover voto' : 'Útil' }}"
                        >
                            @if($commentVoted)
                                <x-heroicon-s-hand-thumb-up class="w-3.5 h-3.5" />
                            @else
                                <x-heroicon-o-hand-thumb-up class="w-3.5 h-3.5" />
                            @endif
                            @if($comment->votes_count > 0)
                                <span>{{ $comment->votes_count }}</span>
                            @endif
                        </button>

                        @auth
                            @if($replyingTo !== $comment->id)
                                <button
                                    wire:click="startReply({{ $comment->id }})"
                                    class="inline-flex items-center gap-1 text-xs text-stone-400 hover:text-amber-600 transition-colors"
                                >
                                    <x-heroicon-o-chat-bubble-left class="w-3.5 h-3.5" />
                                    Responder
                                </button>
                            @endif
                        @endauth
                    </div>

                    {{-- Formulário de resposta inline --}}
                    @if($replyingTo === $comment->id)
                        <div class="mt-3 flex gap-2">
                            <div class="shrink-0 w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center">
                                <span class="text-xs font-bold text-amber-700">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1">
                                <textarea wire:model="replyBody" rows="2"
                                          placeholder="Respondendo a {{ $comment->user->name }}..."
                                          class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 resize-none"
                                          autofocus></textarea>
                                @error('replyBody') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <div class="mt-2 flex gap-2 justify-end">
                                    <button wire:click="cancelReply" type="button"
                                            class="px-3 py-1 text-xs font-medium text-stone-600 hover:text-stone-800 bg-white border border-stone-300 rounded-lg transition-colors duration-150">
                                        Cancelar
                                    </button>
                                    <button wire:click="addReply" wire:loading.attr="disabled" type="button"
                                            class="px-3 py-1 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-xs font-medium rounded-lg transition-colors duration-150">
                                        <span wire:loading.remove wire:target="addReply">Responder</span>
                                        <span wire:loading wire:target="addReply">Enviando...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Replies --}}
                    @if($comment->replies->isNotEmpty())
                        <div class="mt-3 space-y-3 border-l-2 border-stone-200 pl-4">
                            @foreach($comment->replies as $reply)
                                <div class="flex gap-2">
                                    <div class="shrink-0 w-6 h-6 rounded-full bg-stone-200 flex items-center justify-center">
                                        <span class="text-xs font-bold text-stone-600">{{ substr($reply->user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-stone-50 rounded-lg px-3 py-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <a href="{{ route('users.show', $reply->user) }}"
                                                   class="text-xs font-medium text-stone-900 hover:text-amber-700 transition-colors">{{ $reply->user->name }}</a>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-stone-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                    @can('update', $reply)
                                                        <button wire:click="startEdit({{ $reply->id }})"
                                                                class="text-stone-400 hover:text-amber-600 transition-colors" title="Editar">
                                                            <x-heroicon-o-pencil-square class="w-3 h-3" />
                                                        </button>
                                                    @endcan
                                                    @can('delete', $reply)
                                                        <button wire:click="deleteComment({{ $reply->id }})"
                                                                wire:confirm="Remover esta resposta?"
                                                                class="text-stone-400 hover:text-red-500 transition-colors" title="Excluir">
                                                            <x-heroicon-o-trash class="w-3 h-3" />
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>

                                            @if($editingId === $reply->id)
                                                <textarea wire:model="editBody" rows="2"
                                                          class="w-full rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500 resize-none mt-1"></textarea>
                                                @error('editBody') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                                <div class="mt-2 flex gap-2 justify-end">
                                                    <button wire:click="cancelEdit" type="button"
                                                            class="px-3 py-1 text-xs font-medium text-stone-600 hover:text-stone-800 bg-white border border-stone-300 rounded-lg transition-colors duration-150">
                                                        Cancelar
                                                    </button>
                                                    <button wire:click="saveEdit" wire:loading.attr="disabled" type="button"
                                                            class="px-3 py-1 bg-amber-600 hover:bg-amber-700 disabled:opacity-75 text-white text-xs font-medium rounded-lg transition-colors duration-150">
                                                        <span wire:loading.remove wire:target="saveEdit">Salvar</span>
                                                        <span wire:loading wire:target="saveEdit">Salvando...</span>
                                                    </button>
                                                </div>
                                            @else
                                                <p class="text-sm text-stone-700">{{ $reply->body }}</p>
                                            @endif
                                        </div>

                                        {{-- Voto na reply --}}
                                        @php $replyVoted = in_array($reply->id, $userVotedIds); @endphp
                                        <div class="mt-1 ml-1">
                                            <button
                                                wire:click="voteComment({{ $reply->id }})"
                                                class="inline-flex items-center gap-1 text-xs transition-colors {{ $replyVoted ? 'text-amber-600' : 'text-stone-400 hover:text-amber-600' }}"
                                                title="{{ $replyVoted ? 'Remover voto' : 'Útil' }}"
                                            >
                                                @if($replyVoted)
                                                    <x-heroicon-s-hand-thumb-up class="w-3 h-3" />
                                                @else
                                                    <x-heroicon-o-hand-thumb-up class="w-3 h-3" />
                                                @endif
                                                @if($reply->votes_count > 0)
                                                    <span>{{ $reply->votes_count }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-stone-400 text-center py-4">Seja o primeiro a comentar.</p>
        @endforelse
    </div>

    @if($comments->hasPages())
        <div class="mt-5">
            {{ $comments->links() }}
        </div>
    @endif
</div>

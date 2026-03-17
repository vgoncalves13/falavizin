<x-app-layout title="Editar Perfil">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('profile.account') }}"
               class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-700">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Minha conta
            </a>
        </div>

        <h1 class="text-2xl font-bold text-stone-900 mb-6" style="font-family: var(--font-display)">Editar perfil</h1>

        {{-- Dados pessoais --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-5">
            <h2 class="text-base font-semibold text-stone-900 mb-5">Dados pessoais</h2>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-1">Nome</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $user->name) }}"
                           required autofocus autocomplete="name"
                           class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-1">E-mail</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           required autocomplete="username"
                           class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-stone-700 mb-1">
                            Telefone <span class="text-stone-400 font-normal">(opcional)</span>
                        </label>
                        <input type="text" id="phone" name="phone"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="(21) 99999-9999"
                               class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="neighborhood" class="block text-sm font-medium text-stone-700 mb-1">
                            Bairro <span class="text-stone-400 font-normal">(opcional)</span>
                        </label>
                        <input type="text" id="neighborhood" name="neighborhood"
                               value="{{ old('neighborhood', $user->neighborhood) }}"
                               placeholder="Ex: Engenho da Rainha"
                               class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                        @error('neighborhood')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <button type="submit"
                            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Salvar alterações
                    </button>

                    @if(session('status') === 'profile-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition
                           x-init="setTimeout(() => show = false, 2000)"
                           class="text-sm text-green-600">
                            Salvo!
                        </p>
                    @endif
                </div>
            </form>
        </div>

        {{-- Alterar senha --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-5">
            <h2 class="text-base font-semibold text-stone-900 mb-5">Alterar senha</h2>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-stone-700 mb-1">Senha atual</label>
                    <input type="password" id="current_password" name="current_password"
                           autocomplete="current-password"
                           class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-stone-700 mb-1">Nova senha</label>
                        <input type="password" id="password" name="password"
                               autocomplete="new-password"
                               class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                        @error('password', 'updatePassword')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-stone-700 mb-1">Confirmar nova senha</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               autocomplete="new-password"
                               class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <button type="submit"
                            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Alterar senha
                    </button>

                    @if(session('status') === 'password-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition
                           x-init="setTimeout(() => show = false, 2000)"
                           class="text-sm text-green-600">
                            Senha alterada!
                        </p>
                    @endif
                </div>
            </form>
        </div>

        {{-- Excluir conta --}}
        <div class="bg-white rounded-xl border border-red-100 p-6" x-data="{ open: false }">
            <h2 class="text-base font-semibold text-stone-900 mb-1">Excluir conta</h2>
            <p class="text-sm text-stone-500 mb-4">Após excluir sua conta, todos os seus dados serão permanentemente removidos.</p>

            <button type="button" @click="open = true"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                Excluir minha conta
            </button>

            {{-- Confirmation modal --}}
            <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-stone-900/50" @click="open = false" style="display: none;"></div>
            <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="w-full max-w-md bg-white rounded-xl shadow-xl ring-1 ring-stone-200 p-6">
                    <h3 class="text-lg font-semibold text-stone-900 mb-2">Tem certeza?</h3>
                    <p class="text-sm text-stone-500 mb-6">Esta ação não pode ser desfeita. Informe sua senha para confirmar.</p>

                    <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                        @csrf
                        @method('DELETE')

                        <div>
                            <label for="delete_password" class="block text-sm font-medium text-stone-700 mb-1">Senha</label>
                            <input type="password" id="delete_password" name="password"
                                   placeholder="Sua senha atual"
                                   class="w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-900 focus:border-red-400 focus:ring-2 focus:ring-red-400/20 focus:outline-none" />
                            @error('password', 'userDeletion')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="open = false"
                                    class="px-4 py-2 text-sm font-medium text-stone-600 hover:bg-stone-100 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                                Confirmar exclusão
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

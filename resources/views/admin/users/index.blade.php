<x-app-layout title="Gerenciar Usuários">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-stone-900" style="font-family: var(--font-display)">Gerenciar Usuários</h1>
                <p class="text-sm text-stone-500 mt-0.5">{{ $stats['total'] }} usuários cadastrados</p>
            </div>
            <a href="{{ route('admin.moderation.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-600 hover:text-stone-800 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Moderação
            </a>
        </div>

        @session('success')
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ $value }}
            </div>
        @endsession

        {{-- Stats --}}
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                <p class="text-2xl font-bold text-stone-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-stone-500 mt-1">Total</p>
            </div>
            <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                <p class="text-2xl font-bold text-red-600">{{ $stats['admins'] }}</p>
                <p class="text-xs text-stone-500 mt-1">Admins</p>
            </div>
            <div class="bg-white rounded-xl border border-amber-200 p-4 text-center">
                <p class="text-2xl font-bold text-amber-600">{{ $stats['moderators'] }}</p>
                <p class="text-xs text-stone-500 mt-1">Moderadores</p>
            </div>
            <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                <p class="text-2xl font-bold text-stone-600">{{ $stats['users'] }}</p>
                <p class="text-xs text-stone-500 mt-1">Usuários</p>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-3 mb-5">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-stone-400" />
                </div>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Buscar por nome ou email..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500"
                />
            </div>
            <select name="role" class="rounded-lg border-stone-300 text-stone-900 text-sm focus:ring-amber-500 focus:border-amber-500">
                <option value="">Todas as roles</option>
                <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="moderator" {{ $roleFilter === 'moderator' ? 'selected' : '' }}>Moderador</option>
                <option value="user" {{ $roleFilter === 'user' ? 'selected' : '' }}>Usuário</option>
            </select>
            <button type="submit"
                    class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                Filtrar
            </button>
        </form>

        {{-- Lista de usuários --}}
        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-100 bg-stone-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-stone-500 uppercase">Usuário</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-stone-500 uppercase">Role</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-stone-500 uppercase">Posts</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-stone-500 uppercase">Negócios</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-stone-500 uppercase">Desde</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-stone-500 uppercase">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @foreach($users as $user)
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-amber-700">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-stone-900">{{ $user->name }}</p>
                                        <p class="text-xs text-stone-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($user->role->value === 'admin')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full">
                                        <x-heroicon-s-shield-check class="w-3 h-3" />
                                        Admin
                                    </span>
                                @elseif($user->role->value === 'moderator')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                                        <x-heroicon-o-shield-check class="w-3 h-3" />
                                        Moderador
                                    </span>
                                @else
                                    <span class="text-xs text-stone-400">Usuário</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center text-stone-600">{{ $user->posts_count }}</td>
                            <td class="px-5 py-3 text-center text-stone-600">{{ $user->businesses_count }}</td>
                            <td class="px-5 py-3 text-center text-stone-400 text-xs">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="text-xs rounded border-stone-300 text-stone-600 focus:ring-amber-500 focus:border-amber-500 py-1">
                                            <option value="user" {{ $user->role->value === 'user' ? 'selected' : '' }}>Usuário</option>
                                            <option value="moderator" {{ $user->role->value === 'moderator' ? 'selected' : '' }}>Moderador</option>
                                            <option value="admin" {{ $user->role->value === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <button type="submit"
                                                class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                                            Salvar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-stone-300 italic">Você</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>

<x-guest-layout>

    {{-- Heading --}}
    <div class="fade-1" style="margin-bottom:2rem;">
        <h2 style="font-family:'Fraunces',Georgia,serif;font-size:1.875rem;font-weight:700;color:#1c1917;letter-spacing:-.025em;margin:0 0 .4rem;line-height:1.15;">
            Vire um vizinho
        </h2>
        <p style="color:#78716c;font-size:.9375rem;margin:0;">
            Crie sua conta e faça parte da comunidade.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- Nome --}}
        <div class="fade-2">
            <label for="name" class="g-label">Nome completo</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Seu nome"
                class="g-input"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        {{-- E-mail --}}
        <div class="fade-3">
            <label for="email" class="g-label">E-mail</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="voce@exemplo.com"
                class="g-input"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        {{-- Senha --}}
        <div class="fade-4">
            <label for="password" class="g-label">Senha</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Mínimo 8 caracteres"
                class="g-input"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Confirmar senha --}}
        <div class="fade-5">
            <label for="password_confirmation" class="g-label">Confirmar senha</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Repita a senha"
                class="g-input"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        {{-- Submit --}}
        <div class="fade-6">
            <button type="submit" class="g-btn">
                Criar minha conta →
            </button>
        </div>
    </form>

    {{-- Divider + login --}}
    <div class="fade-7" style="margin-top:1.75rem;padding-top:1.75rem;border-top:1px solid #e7e5e4;text-align:center;">
        <span style="font-size:.9rem;color:#78716c;">Já tem conta? </span>
        <a href="{{ route('login') }}"
           style="font-size:.9rem;color:#d97706;font-weight:600;text-decoration:none;transition:color .15s;"
           onmouseover="this.style.color='#b45309'"
           onmouseout="this.style.color='#d97706'">
            Fazer login
        </a>
    </div>

    {{-- Back to home --}}
    <div style="margin-top:1rem;text-align:center;">
        <a href="{{ route('home') }}"
           style="font-size:.8125rem;color:#a8a29e;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:color .15s;"
           onmouseover="this.style.color='#78716c'"
           onmouseout="this.style.color='#a8a29e'">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Voltar para a home
        </a>
    </div>

</x-guest-layout>

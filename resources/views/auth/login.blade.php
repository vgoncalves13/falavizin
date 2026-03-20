<x-guest-layout>

    {{-- Heading --}}
    <div class="fade-1" style="margin-bottom:2rem;">
        <h2 style="font-family:'Fraunces',Georgia,serif;font-size:1.875rem;font-weight:700;color:#1c1917;letter-spacing:-.025em;margin:0 0 .4rem;line-height:1.15;">
            Bem-vindo de volta
        </h2>
        <p style="color:#78716c;font-size:.9375rem;margin:0;">
            Entre e participe da sua comunidade.
        </p>
    </div>

    {{-- Session status --}}
    <x-auth-session-status class="fade-1 mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- E-mail --}}
        <div class="fade-2">
            <label for="email" class="g-label">E-mail</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="voce@exemplo.com"
                class="g-input"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        {{-- Senha --}}
        <div class="fade-3">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.45rem;">
                <label for="password" class="g-label" style="margin-bottom:0;">Senha</label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       style="font-size:.8125rem;color:#d97706;font-weight:500;text-decoration:none;transition:color .15s;"
                       onmouseover="this.style.color='#b45309'"
                       onmouseout="this.style.color='#d97706'">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="g-input"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Lembrar --}}
        <div class="fade-4" style="display:flex;align-items:center;gap:.5rem;">
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                style="width:1rem;height:1rem;accent-color:#d97706;cursor:pointer;"
            />
            <label for="remember_me" style="font-size:.875rem;color:#57534e;cursor:pointer;user-select:none;">
                Lembrar de mim por 30 dias
            </label>
        </div>

        {{-- Submit --}}
        <div class="fade-5">
            <button type="submit" class="g-btn">
                Entrar no bairro →
            </button>
        </div>
    </form>

    {{-- Divider + register --}}
    @if(Route::has('register'))
        <div class="fade-6" style="margin-top:1.75rem;padding-top:1.75rem;border-top:1px solid #e7e5e4;text-align:center;">
            <span style="font-size:.9rem;color:#78716c;">Ainda não é vizinho? </span>
            <a href="{{ route('register') }}"
               style="font-size:.9rem;color:#d97706;font-weight:600;text-decoration:none;transition:color .15s;"
               onmouseover="this.style.color='#b45309'"
               onmouseout="this.style.color='#d97706'">
                Criar conta grátis
            </a>
        </div>
    @endif

    {{-- Back to home --}}
    <div class="fade-7" style="margin-top:1rem;text-align:center;">
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

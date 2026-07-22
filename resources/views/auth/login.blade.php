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

    {{-- Error message (from social auth failures) --}}
    @if(session('error'))
        <div class="fade-1 mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Google Login --}}
    <div class="fade-2">
        <a href="{{ route('auth.google.redirect', ['intended' => url()->previous()]) }}"
           class="g-btn"
           style="display:flex;align-items:center;justify-content:center;gap:.625rem;background:#fff;color:#1c1917;border:1px solid #d6d3d1;font-weight:500;transition:all .15s;"
           onmouseover="this.style.background='#f5f5f4';this.style.borderColor='#a8a29e'"
           onmouseout="this.style.background='#fff';this.style.borderColor='#d6d3d1'">
            <svg width="18" height="18" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continuar com Google
        </a>
    </div>

    {{-- Divider --}}
    <div class="fade-3" style="display:flex;align-items:center;gap:1rem;margin:1.5rem 0;">
        <div style="flex:1;height:1px;background:#e7e5e4;"></div>
        <span style="font-size:.8125rem;color:#a8a29e;font-weight:500;">ou</span>
        <div style="flex:1;height:1px;background:#e7e5e4;"></div>
    </div>

    <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- E-mail --}}
        <div class="fade-4">
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
        <div class="fade-5">
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
        <div class="fade-6" style="display:flex;align-items:center;gap:.5rem;">
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
        <div class="fade-7">
            <button type="submit" class="g-btn">
                Entrar no bairro →
            </button>
        </div>
    </form>

    {{-- Divider + register --}}
    @if(Route::has('register'))
        <div class="fade-8" style="margin-top:1.75rem;padding-top:1.75rem;border-top:1px solid #e7e5e4;text-align:center;">
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
    <div class="fade-9" style="margin-top:1rem;text-align:center;">
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

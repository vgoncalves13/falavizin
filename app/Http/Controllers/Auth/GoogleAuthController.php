<?php

namespace App\Http\Controllers\Auth;

use App\Actions\HandleGoogleAuthentication;
use App\Http\Controllers\Controller;
use App\Models\Neighborhood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        $intended = request('intended');

        if ($intended && Str::startsWith($intended, '/') && ! Str::startsWith($intended, '//')) {
            session()->put('url.intended', $intended);
        }

        $neighborhood = Neighborhood::query()
            ->active()
            ->find(session('current_neighborhood_id'));

        session()->put('oauth_neighborhood_id', $neighborhood?->id);

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(HandleGoogleAuthentication $action): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            Log::warning('Google auth failed: invalid state', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', 'Não foi possível entrar com o Google. Tente novamente ou utilize seu e-mail.');
        } catch (\Throwable $e) {
            Log::error('Google auth failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', 'Não foi possível entrar com o Google. Tente novamente ou utilize seu e-mail.');
        }

        if (empty($googleUser->getId())) {
            Log::warning('Google auth failed: missing provider ID');

            return redirect()->route('login')
                ->with('error', 'Não foi possível entrar com o Google. Tente novamente ou utilize seu e-mail.');
        }

        $email = strtolower(trim($googleUser->getEmail() ?? ''));

        if (empty($email)) {
            Log::warning('Google auth failed: missing email', [
                'provider_user_id' => $googleUser->getId(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Não foi possível obter seu e-mail do Google. Tente novamente ou utilize seu e-mail.');
        }

        $neighborhood = Neighborhood::query()
            ->active()
            ->find(session('oauth_neighborhood_id'));

        try {
            $user = $action->execute($googleUser, $neighborhood);
        } catch (\Throwable $e) {
            Log::error('Google auth user resolution failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', 'Não foi possível entrar com o Google. Tente novamente ou utilize seu e-mail.');
        }

        if (! $user->primaryNeighborhood && ! $user->neighborhood) {
            return redirect()->route('neighborhoods.select');
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        Log::info('Social login completed', [
            'provider' => 'google',
            'user_id' => $user->id,
        ]);

        $intended = session()->pull('url.intended');

        if ($intended && ! Str::startsWith($intended, '/') || Str::startsWith($intended, '//')) {
            $intended = null;
        }

        return redirect($intended ?? route('home'));
    }
}

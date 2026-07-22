<?php

namespace App\Actions;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleGoogleAuthentication
{
    public function execute(SocialiteUser $googleUser): User
    {
        $providerUserId = $googleUser->getId();
        $email = strtolower(trim($googleUser->getEmail() ?? ''));

        if (empty($providerUserId)) {
            throw new \RuntimeException('Google user ID is missing.');
        }

        $existingAccount = SocialAccount::findByProvider('google', $providerUserId);

        if ($existingAccount) {
            Log::info('Social login completed', [
                'provider' => 'google',
                'type' => 'existing_account',
                'user_id' => $existingAccount->user_id,
            ]);

            return $existingAccount->user;
        }

        return DB::transaction(function () use ($googleUser, $providerUserId, $email) {
            $user = $this->findOrCreateUser($email, $googleUser);

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => 'google',
                'provider_user_id' => $providerUserId,
                'provider_email' => $email ?: null,
                'avatar_url' => $googleUser->getAvatar(),
            ]);

            Log::info('Social account linked', [
                'provider' => 'google',
                'user_id' => $user->id,
                'was_existing' => $user->wasRecentlyCreated ? 'no' : 'yes',
            ]);

            return $user;
        });
    }

    private function findOrCreateUser(string $email, SocialiteUser $googleUser): User
    {
        if ($email) {
            $existingUser = User::query()->where('email', $email)->first();

            if ($existingUser) {
                Log::info('Social user linked to existing account', [
                    'provider' => 'google',
                    'user_id' => $existingUser->id,
                ]);

                if (! $existingUser->avatar_url && $googleUser->getAvatar()) {
                    $existingUser->update(['avatar_url' => $googleUser->getAvatar()]);
                }

                return $existingUser;
            }
        }

        $user = User::create([
            'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Usuário',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => null,
            'avatar_url' => $googleUser->getAvatar(),
        ]);

        Log::info('Social user created', [
            'provider' => 'google',
            'user_id' => $user->id,
        ]);

        return $user;
    }
}

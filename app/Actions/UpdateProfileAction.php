<?php

namespace App\Actions;

use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class UpdateProfileAction
{
    public function execute(User $user, array $data): void
    {
        $avatar = $data['avatar'] ?? null;
        unset($data['avatar']);

        $oldAvatarPath = $user->avatar_url;
        $newAvatarPath = null;

        try {
            if ($avatar instanceof UploadedFile) {
                $image = (new ImageManager(new Driver))
                    ->read($avatar)
                    ->scaleDown(width: 512, height: 512);
                $newAvatarPath = 'avatars/'.Str::uuid().'.webp';

                if (! Storage::disk('public')->put(
                    $newAvatarPath,
                    (string) $image->encodeByExtension('webp', quality: 85),
                )) {
                    throw new RuntimeException('Não foi possível salvar a foto de perfil.');
                }

                $data['avatar_url'] = $newAvatarPath;
            }

            $emailChanged = ($data['email'] ?? $user->email) !== $user->email;

            if (array_key_exists('neighborhood_id', $data) && $data['neighborhood_id'] !== null) {
                $neighborhood = Neighborhood::query()->active()->findOrFail($data['neighborhood_id']);
                $data['neighborhood'] = $neighborhood->name;
            }

            $user->fill($data);

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();
        } catch (Throwable $exception) {
            if ($newAvatarPath) {
                Storage::disk('public')->delete($newAvatarPath);
            }

            throw $exception;
        }

        if (
            $newAvatarPath
            && $oldAvatarPath
            && Str::startsWith($oldAvatarPath, 'avatars/')
        ) {
            Storage::disk('public')->delete($oldAvatarPath);
        }
    }
}

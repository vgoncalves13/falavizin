<?php

namespace App\Actions;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Neighborhood;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class CreateBusinessAction
{
    public function execute(User $user, Neighborhood $neighborhood, array $data, ?UploadedFile $coverPhoto = null): Business
    {
        throw_unless($neighborhood->is_active, ValidationException::withMessages([
            'name' => 'Este bairro não está mais ativo.',
        ]));

        $rateKey = "create-business:{$user->getKey()}";
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            throw ValidationException::withMessages([
                'name' => 'Você atingiu o limite diário de cadastros de negócios.',
            ]);
        }

        $preparedCover = $coverPhoto ? $this->prepareCoverPhoto($coverPhoto) : null;
        $storedPath = null;

        try {
            $business = DB::transaction(function () use ($user, $neighborhood, $data, $preparedCover, &$storedPath): Business {
                $business = $user->businesses()->create([
                    'category_id' => $data['category_ids'][0],
                    'neighborhood_id' => $neighborhood->id,
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'whatsapp' => $data['whatsapp'] ?? null,
                    'address' => $data['address'] ?? null,
                    'neighborhood' => $neighborhood->name,
                    'city' => $data['city'] ?? $neighborhood->city,
                    'opening_hours' => $data['opening_hours'] ?? null,
                    'website' => $data['website'] ?? null,
                    'status' => BusinessStatus::Pending,
                    'claimed' => true,
                    'claimed_at' => now(),
                ]);

                $business->categories()->sync($data['category_ids']);

                if ($preparedCover) {
                    $storedPath = 'businesses/'.$business->id.'/cover.'.$preparedCover['extension'];

                    if (! Storage::disk('public')->put($storedPath, $preparedCover['contents'])) {
                        throw new RuntimeException('Não foi possível salvar a foto de capa.');
                    }

                    $business->photos()->create([
                        'path' => $storedPath,
                        'is_cover' => true,
                        'sort_order' => 0,
                    ]);
                }

                return $business;
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        RateLimiter::hit($rateKey, 86_400);

        $this->notifyAdmins($business->name);

        return $business;
    }

    private function notifyAdmins(string $name): void
    {
        $admins = User::where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new NewContentNotification('business', $name));
        Cache::forget('admin:moderation_count');
    }

    /** @return array{extension: string, contents: string} */
    private function prepareCoverPhoto(UploadedFile $file): array
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);
        $image->scaleDown(width: 1200);
        $extension = strtolower($file->getClientOriginalExtension());

        return [
            'extension' => $extension,
            'contents' => (string) $image->encodeByExtension($extension, quality: 85),
        ];
    }
}

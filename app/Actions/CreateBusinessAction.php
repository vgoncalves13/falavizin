<?php

namespace App\Actions;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class CreateBusinessAction
{
    public function execute(User $user, array $data, ?UploadedFile $coverPhoto = null): Business
    {
        $preparedCover = $coverPhoto ? $this->prepareCoverPhoto($coverPhoto) : null;
        $storedPath = null;

        try {
            $business = DB::transaction(function () use ($user, $data, $preparedCover, &$storedPath): Business {
                $business = $user->businesses()->create([
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'whatsapp' => $data['whatsapp'] ?? null,
                    'address' => $data['address'] ?? null,
                    'neighborhood' => $data['neighborhood'],
                    'city' => $data['city'] ?? '',
                    'opening_hours' => $data['opening_hours'] ?? null,
                    'website' => $data['website'] ?? null,
                    'status' => BusinessStatus::Pending,
                    'claimed' => true,
                    'claimed_at' => now(),
                ]);

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

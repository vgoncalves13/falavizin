<?php

namespace App\Actions;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class CreateBusinessAction
{
    public function execute(User $user, array $data, ?UploadedFile $coverPhoto = null): Business
    {
        $business = $user->businesses()->create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'address' => $data['address'] ?? null,
            'neighborhood' => $data['neighborhood'],
            'city' => $data['city'] ?? '',
            'website' => $data['website'] ?? null,
            'status' => BusinessStatus::Pending,
            'claimed' => true,
            'claimed_at' => now(),
        ]);

        if ($coverPhoto) {
            $this->saveCoverPhoto($business, $coverPhoto);
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
    }

    private function saveCoverPhoto(Business $business, UploadedFile $file): void
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);
        $image->scaleDown(width: 1200);

        $path = 'businesses/'.$business->id.'/cover.'.$file->getClientOriginalExtension();
        Storage::disk('public')->makeDirectory('businesses/'.$business->id);
        $image->save(storage_path('app/public/'.$path));

        $business->photos()->create([
            'path' => $path,
            'is_cover' => true,
            'sort_order' => 0,
        ]);
    }
}

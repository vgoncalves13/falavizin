<?php

namespace App\Actions;

use App\Models\Business;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UpdateBusinessAction
{
    public function execute(Business $business, array $data, ?UploadedFile $coverPhoto = null): Business
    {
        $business->update([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'address' => $data['address'] ?? null,
            'neighborhood' => $data['neighborhood'],
            'city' => $data['city'] ?? '',
            'website' => $data['website'] ?? null,
        ]);

        if ($coverPhoto) {
            $this->replaceCoverPhoto($business, $coverPhoto);
        }

        return $business->fresh();
    }

    private function replaceCoverPhoto(Business $business, UploadedFile $file): void
    {
        $existing = $business->coverPhoto;
        if ($existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }

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

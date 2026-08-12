<?php

namespace App\Actions;

use App\Models\Business;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class UpdateBusinessAction
{
    public function execute(Business $business, array $data, ?UploadedFile $coverPhoto = null): Business
    {
        $preparedCover = $coverPhoto ? $this->prepareCoverPhoto($coverPhoto) : null;
        $existingCover = $business->coverPhoto;
        $oldPath = $existingCover?->path;
        $newPath = null;

        try {
            DB::transaction(function () use ($business, $data, $preparedCover, $existingCover, &$newPath): void {
                $updateData = [
                    'category_id' => $data['category_ids'][0],
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'whatsapp' => $data['whatsapp'] ?? null,
                    'instagram' => $data['instagram'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? '',
                    'opening_hours' => $data['opening_hours'] ?? null,
                    'website' => $data['website'] ?? null,
                ];

                if (isset($data['neighborhood'])) {
                    $updateData['neighborhood'] = $data['neighborhood'];
                }

                $business->update($updateData);

                $business->categories()->sync($data['category_ids']);

                if ($preparedCover) {
                    $newPath = 'businesses/'.$business->id.'/cover_'.Str::uuid().'.'.$preparedCover['extension'];

                    if (! Storage::disk('public')->put($newPath, $preparedCover['contents'])) {
                        throw new RuntimeException('Não foi possível salvar a foto de capa.');
                    }

                    if ($existingCover) {
                        $existingCover->update(['path' => $newPath]);
                    } else {
                        $business->photos()->create([
                            'path' => $newPath,
                            'is_cover' => true,
                            'sort_order' => 0,
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $business->fresh();
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

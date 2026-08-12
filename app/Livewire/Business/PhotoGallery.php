<?php

namespace App\Livewire\Business;

use App\Models\Business;
use App\Models\BusinessPhoto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;
use Throwable;

class PhotoGallery extends Component
{
    use WithFileUploads;

    public Business $business;

    /** @var array<int, mixed> */
    public array $newPhotos = [];

    protected function rules(): array
    {
        return [
            'newPhotos.*' => ['image', 'max:5120'],
        ];
    }

    protected function messages(): array
    {
        return [
            'newPhotos.*.image' => 'Apenas imagens são permitidas.',
            'newPhotos.*.max' => 'Cada imagem não pode ter mais de 5MB.',
        ];
    }

    public function savePhotos(): void
    {
        Gate::authorize('update', $this->business);

        $this->validate();

        $manager = new ImageManager(new Driver);
        $nextOrder = ($this->business->photos()->max('sort_order') ?? 0) + 1;
        $preparedPhotos = [];

        foreach ($this->newPhotos as $photo) {
            $image = $manager->read($photo->getRealPath());
            $image->scaleDown(width: 1200);
            $extension = strtolower($photo->getClientOriginalExtension());

            $preparedPhotos[] = [
                'extension' => $extension,
                'contents' => (string) $image->encodeByExtension($extension, quality: 85),
            ];
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($preparedPhotos, &$storedPaths, &$nextOrder): void {
                foreach ($preparedPhotos as $photo) {
                    $path = 'businesses/'.$this->business->id.'/photo_'.uniqid().'.'.$photo['extension'];

                    if (! Storage::disk('public')->put($path, $photo['contents'])) {
                        throw new RuntimeException('Não foi possível salvar uma foto da galeria.');
                    }

                    $storedPaths[] = $path;

                    $this->business->photos()->create([
                        'path' => $path,
                        'is_cover' => false,
                        'sort_order' => $nextOrder++,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        $this->newPhotos = [];
        $this->business->unsetRelation('photos');
    }

    public function setCover(int $photoId): void
    {
        Gate::authorize('update', $this->business);

        $photo = $this->business->photos()->findOrFail($photoId);

        DB::transaction(function () use ($photo): void {
            $this->business->photos()->update(['is_cover' => false]);
            $photo->update(['is_cover' => true, 'sort_order' => 0]);
        });

        $this->business->unsetRelation('photos');
        $this->business->unsetRelation('coverPhoto');
    }

    public function delete(int $photoId): void
    {
        Gate::authorize('update', $this->business);

        $photo = BusinessPhoto::query()->where('business_id', $this->business->id)->findOrFail($photoId);

        DB::transaction(fn () => $photo->delete());
        Storage::disk('public')->delete($photo->path);

        $this->business->unsetRelation('photos');
        $this->business->unsetRelation('coverPhoto');
    }

    public function render()
    {
        $photos = $this->business->photos()->orderBy('sort_order')->get();
        $canManage = auth()->check() && auth()->user()->can('update', $this->business);

        return view('livewire.business.photo-gallery', compact('photos', 'canManage'));
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Business\PhotoGallery;
use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_and_delete_gallery_photo(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $owner->id]);

        Livewire::actingAs($owner)
            ->test(PhotoGallery::class, ['business' => $business])
            ->set('newPhotos', [UploadedFile::fake()->image('gallery.jpg')])
            ->call('savePhotos');

        $photo = $business->photos()->firstOrFail();
        Storage::disk('public')->assertExists($photo->path);

        Livewire::actingAs($owner)
            ->test(PhotoGallery::class, ['business' => $business])
            ->call('delete', $photo->id);

        Storage::disk('public')->assertMissing($photo->path);
        $this->assertDatabaseMissing('business_photos', ['id' => $photo->id]);
    }

    public function test_invalid_cover_id_does_not_clear_current_cover(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $owner->id]);
        $cover = BusinessPhoto::factory()->create([
            'business_id' => $business->id,
            'is_cover' => true,
        ]);

        try {
            Livewire::actingAs($owner)
                ->test(PhotoGallery::class, ['business' => $business])
                ->call('setCover', 999999);
        } catch (\Throwable) {
            // The missing target must fail before the current cover changes.
        }

        $this->assertTrue($cover->fresh()->is_cover);
    }
}

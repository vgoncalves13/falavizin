<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Services\GooglePlacesService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class EnrichBusinessFromGoogle implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $timeout = 60;

    public function __construct(public readonly int $businessId) {}

    public function handle(GooglePlacesService $service): void
    {
        $business = Business::find($this->businessId);

        if (! $business || ! $business->google_place_id) {
            return;
        }

        try {
            $details = $service->getPlaceDetails($business->google_place_id);
        } catch (\RuntimeException $e) {
            Log::warning("EnrichBusinessFromGoogle: falha ao buscar detalhes do place {$business->google_place_id}: ".$e->getMessage());

            throw $e;
        }

        $updates = [];

        if (empty($business->phone) && ! empty($details['nationalPhoneNumber'])) {
            $updates['phone'] = [$details['nationalPhoneNumber']];
        }

        if (empty($business->website) && ! empty($details['websiteUri'])) {
            $updates['website'] = $details['websiteUri'];
        }

        if (empty($business->opening_hours) && ! empty($details['regularOpeningHours']['weekdayDescriptions'])) {
            $updates['opening_hours'] = $this->parseOpeningHours($details['regularOpeningHours']['weekdayDescriptions']);
        }

        if (! empty($updates)) {
            $business->update($updates);
        }

        $this->importPhotos($business, $service, $details);
    }

    /**
     * Converts Google Places weekdayDescriptions (e.g. "Segunda-feira: 08:00 – 18:00")
     * into the structured format used by this application.
     *
     * @param  array<int, string>  $weekdayDescriptions
     * @return array<int, array{day: string, open: string, close: string, closed: bool}>
     */
    private function parseOpeningHours(array $weekdayDescriptions): array
    {
        $days = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo'];

        $byDay = [];
        foreach ($weekdayDescriptions as $desc) {
            $parts = explode(': ', $desc, 2);
            if (count($parts) === 2) {
                $byDay[trim($parts[0])] = trim($parts[1]);
            }
        }

        $result = [];
        foreach ($days as $day) {
            $value = $byDay[$day] ?? null;
            $closed = $value === null || in_array(strtolower($value), ['fechado', 'closed']);
            $open = '';
            $close = '';

            if (! $closed) {
                // Google uses U+2009 (thin space) + U+2013 (en dash) as time separator
                $times = preg_split('/\s*[\x{2013}\x{2014}]\s*/u', $value, 2);
                $open = trim($times[0] ?? '');
                $close = trim($times[1] ?? '');
            }

            $result[] = compact('day', 'open', 'close', 'closed');
        }

        return $result;
    }

    private function importPhotos(Business $business, GooglePlacesService $service, array $details): void
    {
        if ($business->photos()->exists()) {
            return;
        }

        $photos = $details['photos'] ?? [];

        if (empty($photos)) {
            return;
        }

        $manager = new ImageManager(new Driver);
        $sortOrder = 0;

        foreach ($photos as $photo) {
            $photoName = $photo['name'] ?? null;

            if (! $photoName) {
                continue;
            }

            $photoUri = $service->getPhotoUri($photoName);

            if (! $photoUri) {
                continue;
            }

            try {
                $imageContent = Http::timeout(30)->get($photoUri)->body();

                $image = $manager->read($imageContent);
                $image->scaleDown(width: 1200);

                $filename = 'businesses/'.uniqid('google_', true).'.jpg';
                Storage::disk('public')->put($filename, $image->toJpeg(85));

                BusinessPhoto::create([
                    'business_id' => $business->id,
                    'path' => $filename,
                    'is_cover' => $sortOrder === 0,
                    'sort_order' => $sortOrder,
                ]);

                $sortOrder++;
            } catch (\Throwable $e) {
                Log::warning("EnrichBusinessFromGoogle: falha ao importar foto do business {$business->id}: ".$e->getMessage());
            }
        }
    }
}

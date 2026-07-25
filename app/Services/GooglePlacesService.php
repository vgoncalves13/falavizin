<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GooglePlacesService
{
    public function getPlaceDetails(string $placeId): array
    {
        $key = config('services.rapidapi.key');
        $host = config('services.rapidapi.google_places_host');

        $response = Http::connectTimeout(5)->timeout(20)->withHeaders([
            'x-rapidapi-key' => $key,
            'x-rapidapi-host' => $host,
            'X-Goog-FieldMask' => '*',
        ])->get("https://{$host}/v1/places/{$placeId}", ['languageCode' => 'pt-BR']);

        if (! $response->successful()) {
            throw new \RuntimeException("Falha ao buscar detalhes do place {$placeId}: ".$response->status());
        }

        return $response->json();
    }

    public function getPhotoUri(string $photoName): ?string
    {
        $key = config('services.rapidapi.key');
        $host = config('services.rapidapi.google_places_host');

        $response = Http::connectTimeout(5)->timeout(20)->withHeaders([
            'x-rapidapi-key' => $key,
            'x-rapidapi-host' => $host,
        ])->get("https://{$host}/v1/{$photoName}/media", [
            'maxHeightPx' => 1200,
            'maxWidthPx' => 1200,
            'skipHttpRedirect' => 'true',
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('photoUri');
    }

    public function searchNearby(
        float $lat,
        float $lng,
        float $radius = 1000,
        array $includedTypes = [],
        int $maxResults = 20,
    ): Collection {
        $key = config('services.rapidapi.key');
        $host = config('services.rapidapi.google_places_host');

        $body = [
            'locationRestriction' => [
                'circle' => [
                    'center' => ['latitude' => $lat, 'longitude' => $lng],
                    'radius' => $radius,
                ],
            ],
            'maxResultCount' => min($maxResults, 20),
        ];

        if (! empty($includedTypes)) {
            $body['includedTypes'] = $includedTypes;
        }

        $response = Http::connectTimeout(5)->timeout(20)->withHeaders([
            'x-rapidapi-key' => $key,
            'x-rapidapi-host' => $host,
            'Content-Type' => 'application/json',
            'X-Goog-FieldMask' => '*',
        ])->post("https://{$host}/v1/places:searchNearby", $body);

        if (! $response->successful()) {
            throw new \RuntimeException('Falha na requisição à Google Places API: '.$response->status());
        }

        $places = $response->json('places', []);

        $results = collect($places)->map(fn (array $place) => [
            'place_id' => $place['id'] ?? null,
            'name' => $place['displayName']['text'] ?? 'Sem nome',
            'address' => $place['formattedAddress'] ?? null,
            'lat' => $place['location']['latitude'] ?? null,
            'lng' => $place['location']['longitude'] ?? null,
            'phone' => $place['nationalPhoneNumber'] ?? null,
            'website' => $place['websiteUri'] ?? null,
            'types' => $place['types'] ?? [],
            'already_imported' => false,
        ])->filter(fn (array $p) => ! empty($p['place_id']))->values();

        return $results;
    }
}

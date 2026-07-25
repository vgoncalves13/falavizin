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

    /**
     * Search nearby with full control over parameters.
     *
     * @return array{results: Collection, isTruncated: bool}
     */
    public function searchNearby(
        float $lat,
        float $lng,
        float $radius = 1000,
        array $includedTypes = [],
        array $includedPrimaryTypes = [],
        array $excludedTypes = [],
        string $rankPreference = 'DISTANCE',
        int $maxResults = 20,
    ): array {
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
            'rankPreference' => $rankPreference,
            'languageCode' => 'pt-BR',
            'regionCode' => 'BR',
        ];

        if (! empty($includedTypes)) {
            $body['includedTypes'] = $includedTypes;
        }

        if (! empty($includedPrimaryTypes)) {
            $body['includedPrimaryTypes'] = $includedPrimaryTypes;
        }

        if (! empty($excludedTypes)) {
            $body['excludedTypes'] = $excludedTypes;
        }

        $response = Http::connectTimeout(5)->timeout(20)->withHeaders([
            'x-rapidapi-key' => $key,
            'x-rapidapi-host' => $host,
            'Content-Type' => 'application/json',
            'X-Goog-FieldMask' => '*',
        ])->post("https://{$host}/v1/places:searchNearby", $body);

        if (! $response->successful()) {
            $status = $response->status();

            if ($status === 429) {
                throw new \RuntimeException('Rate limit atingido (HTTP 429)');
            }

            throw new \RuntimeException("Falha na API Google Places (HTTP {$status})");
        }

        $places = $response->json('places', []);
        $isTruncated = count($places) >= 20;

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

        return [
            'results' => $results,
            'isTruncated' => $isTruncated,
        ];
    }

    /**
     * Legacy searchNearby for backward compatibility.
     */
    public function searchNearbySimple(
        float $lat,
        float $lng,
        float $radius = 1000,
        array $includedTypes = [],
        int $maxResults = 20,
    ): Collection {
        $response = $this->searchNearby(
            lat: $lat,
            lng: $lng,
            radius: $radius,
            includedTypes: $includedTypes,
            maxResults: $maxResults,
        );

        return $response['results'];
    }
}

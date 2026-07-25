<?php

namespace App\Services\Import;

class RegionFilter
{
    public function isInsideRegion(
        float $placeLat,
        float $placeLng,
        float $centerLat,
        float $centerLng,
        float $radius,
    ): bool {
        return $this->haversine($placeLat, $placeLng, $centerLat, $centerLng) <= $radius;
    }

    /**
     * Filter an array of places, keeping only those inside the region.
     */
    public function filterPlaces(
        array $places,
        float $centerLat,
        float $centerLng,
        float $radius,
    ): array {
        return array_values(array_filter(
            $places,
            fn (array $place) => $this->isInsideRegion(
                $place['lat'],
                $place['lng'],
                $centerLat,
                $centerLng,
                $radius,
            )
        ));
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6_371_000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

<?php

namespace App\Services\Import;

class GridGenerator
{
    /**
     * Generate a grid of search points covering a circular region.
     *
     * @return array<int, array{lat: float, lng: float, radius: float, depth: int}>
     */
    public function generate(
        float $centerLat,
        float $centerLng,
        float $regionRadius,
        float $cellRadius,
        int $overlapPercent = 10,
    ): array {
        $overlapFactor = 1 - ($overlapPercent / 100);
        $effectiveRadius = $cellRadius * 1.1;
        $step = $cellRadius * sqrt(2) * $overlapFactor;

        $stepLat = $this->metersToLat($step);
        $stepLng = $this->metersToLng($step, $centerLat);

        $regionLatSpan = $this->metersToLat($regionRadius * 2);
        $regionLngSpan = $this->metersToLng($regionRadius * 2, $centerLat);

        $rows = (int) ceil($regionLatSpan / $stepLat);
        $cols = (int) ceil($regionLngSpan / $stepLng);

        $cells = [];

        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                $lat = $centerLat + ($i - ($rows - 1) / 2) * $stepLat;
                $lng = $centerLng + ($j - ($cols - 1) / 2) * $stepLng;

                if ($this->circlesIntersect($centerLat, $centerLng, $regionRadius, $lat, $lng, $effectiveRadius)) {
                    $cells[] = [
                        'lat' => round($lat, 7),
                        'lng' => round($lng, 7),
                        'radius' => $effectiveRadius,
                        'depth' => 0,
                    ];
                }
            }
        }

        return $cells;
    }

    private function circlesIntersect(
        float $lat1, float $lng1, float $r1,
        float $lat2, float $lng2, float $r2,
    ): bool {
        $distance = $this->haversine($lat1, $lng1, $lat2, $lng2);

        return $distance <= ($r1 + $r2);
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

    private function metersToLat(float $meters): float
    {
        return $meters / 111_320;
    }

    private function metersToLng(float $meters, float $lat): float
    {
        return $meters / (111_320 * cos(deg2rad($lat)));
    }
}

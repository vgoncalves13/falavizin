<?php

namespace App\Services\Import;

class CellSubdivider
{
    public function __construct(
        private readonly float $minRadius = 100,
        private readonly int $maxDepth = 4,
    ) {}

    /**
     * Subdivide a saturated cell into 4 children.
     *
     * @return array<int, array{lat: float, lng: float, radius: float, depth: int}>
     */
    public function subdivide(array $cell): array
    {
        $radius = $cell['radius'];
        $depth = $cell['depth'] ?? 0;

        if ($radius / 2 < $this->minRadius) {
            return [];
        }

        if ($depth >= $this->maxDepth) {
            return [];
        }

        $childRadius = $radius / 2;
        $offset = $childRadius * 0.4;

        $offsetLat = $offset / 111_320;
        $offsetLng = $offset / (111_320 * cos(deg2rad($cell['lat'])));

        return [
            [
                'lat' => round($cell['lat'] + $offsetLat, 7),
                'lng' => round($cell['lng'] + $offsetLng, 7),
                'radius' => $childRadius,
                'depth' => $depth + 1,
            ],
            [
                'lat' => round($cell['lat'] + $offsetLat, 7),
                'lng' => round($cell['lng'] - $offsetLng, 7),
                'radius' => $childRadius,
                'depth' => $depth + 1,
            ],
            [
                'lat' => round($cell['lat'] - $offsetLat, 7),
                'lng' => round($cell['lng'] + $offsetLng, 7),
                'radius' => $childRadius,
                'depth' => $depth + 1,
            ],
            [
                'lat' => round($cell['lat'] - $offsetLat, 7),
                'lng' => round($cell['lng'] - $offsetLng, 7),
                'radius' => $childRadius,
                'depth' => $depth + 1,
            ],
        ];
    }

    public function canSubdivide(array $cell): bool
    {
        $radius = $cell['radius'];
        $depth = $cell['depth'] ?? 0;

        return ($radius / 2) >= $this->minRadius && $depth < $this->maxDepth;
    }
}

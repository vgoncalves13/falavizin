<?php

namespace Tests\Feature\Import;

use App\Services\Import\GridGenerator;
use PHPUnit\Framework\TestCase;

class GridGeneratorTest extends TestCase
{
    private GridGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new GridGenerator;
    }

    public function test_grid_covers_the_region(): void
    {
        $centerLat = -22.90;
        $centerLng = -43.30;
        $regionRadius = 1000;

        $cells = $this->generator->generate($centerLat, $centerLng, $regionRadius, 500);

        $this->assertNotEmpty($cells);

        foreach ($cells as $cell) {
            $distance = $this->haversine($centerLat, $centerLng, $cell['lat'], $cell['lng']);
            $this->assertLessThanOrEqual(
                $regionRadius + $cell['radius'],
                $distance,
                'Cell at ('.$cell['lat'].', '.$cell['lng'].') is outside region coverage'
            );
        }
    }

    public function test_grid_has_sufficient_overlap(): void
    {
        $cells = $this->generator->generate(-22.90, -43.30, 1000, 500, 10);

        $this->assertGreaterThan(1, $cells);

        for ($i = 0; $i < count($cells) - 1; $i++) {
            for ($j = $i + 1; $j < count($cells); $j++) {
                $distance = $this->haversine(
                    $cells[$i]['lat'], $cells[$i]['lng'],
                    $cells[$j]['lat'], $cells[$j]['lng']
                );
                $combinedRadius = $cells[$i]['radius'] + $cells[$j]['radius'];

                if ($distance < $combinedRadius) {
                    $overlapPercent = (($combinedRadius - $distance) / min($cells[$i]['radius'], $cells[$j]['radius'])) * 100;
                    $this->assertGreaterThanOrEqual(5, $overlapPercent, 'Overlap too small between cells '.$i.' and '.$j);
                }
            }
        }
    }

    public function test_cell_count_scales_with_radius(): void
    {
        $smallGrid = $this->generator->generate(-22.90, -43.30, 500, 300);
        $largeGrid = $this->generator->generate(-22.90, -43.30, 2000, 300);

        $this->assertGreaterThan(count($smallGrid), count($largeGrid));
    }

    public function test_all_cells_have_depth_zero(): void
    {
        $cells = $this->generator->generate(-22.90, -43.30, 1000, 500);

        foreach ($cells as $cell) {
            $this->assertSame(0, $cell['depth']);
        }
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6_371_000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

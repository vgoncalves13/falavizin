<?php

namespace Tests\Feature\Import;

use App\Services\Import\RegionFilter;
use PHPUnit\Framework\TestCase;

class RegionFilterTest extends TestCase
{
    private RegionFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new RegionFilter;
    }

    public function test_accepts_result_inside_region(): void
    {
        $result = $this->filter->isInsideRegion(
            -22.9010, -43.3010,
            -22.9000, -43.3000,
            1000,
        );

        $this->assertTrue($result);
    }

    public function test_rejects_result_outside_region(): void
    {
        $result = $this->filter->isInsideRegion(
            -22.9500, -43.3500,
            -22.9000, -43.3000,
            1000,
        );

        $this->assertFalse($result);
    }

    public function test_accepts_result_on_boundary(): void
    {
        $result = $this->filter->isInsideRegion(
            -22.9050, -43.3000,
            -22.9000, -43.3000,
            1000,
        );

        $this->assertTrue($result);
    }

    public function test_filter_places_array(): void
    {
        $places = [
            ['lat' => -22.901, 'lng' => -43.301, 'name' => 'Inside'],
            ['lat' => -22.95, 'lng' => -43.35, 'name' => 'Outside'],
            ['lat' => -22.902, 'lng' => -43.302, 'name' => 'Also inside'],
        ];

        $filtered = $this->filter->filterPlaces($places, -22.90, -43.30, 1000);

        $this->assertCount(2, $filtered);
        $this->assertSame('Inside', $filtered[0]['name']);
        $this->assertSame('Also inside', $filtered[1]['name']);
    }
}

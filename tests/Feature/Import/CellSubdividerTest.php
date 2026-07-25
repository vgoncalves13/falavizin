<?php

namespace Tests\Feature\Import;

use App\Services\Import\CellSubdivider;
use PHPUnit\Framework\TestCase;

class CellSubdividerTest extends TestCase
{
    public function test_subdivides_cell_with_20_results(): void
    {
        $subdivider = new CellSubdivider(minRadius: 100, maxDepth: 4);

        $cell = ['lat' => -22.90, 'lng' => -43.30, 'radius' => 500, 'depth' => 0];

        $children = $subdivider->subdivide($cell);

        $this->assertCount(4, $children);

        foreach ($children as $child) {
            $this->assertEquals(250.0, $child['radius']);
            $this->assertSame(1, $child['depth']);
            $this->assertNotSame($cell['lat'], $child['lat']);
            $this->assertNotSame($cell['lng'], $child['lng']);
        }
    }

    public function test_does_not_subdivide_below_20_results(): void
    {
        $subdivider = new CellSubdivider(minRadius: 100, maxDepth: 4);

        $cell = ['lat' => -22.90, 'lng' => -43.30, 'radius' => 500, 'depth' => 0];

        $this->assertTrue($subdivider->canSubdivide($cell));
    }

    public function test_stops_at_min_radius(): void
    {
        $subdivider = new CellSubdivider(minRadius: 100, maxDepth: 4);

        $cell = ['lat' => -22.90, 'lng' => -43.30, 'radius' => 150, 'depth' => 0];

        $children = $subdivider->subdivide($cell);

        $this->assertEmpty($children);
        $this->assertFalse($subdivider->canSubdivide($cell));
    }

    public function test_stops_at_max_depth(): void
    {
        $subdivider = new CellSubdivider(minRadius: 10, maxDepth: 2);

        $cell = ['lat' => -22.90, 'lng' => -43.30, 'radius' => 500, 'depth' => 2];

        $children = $subdivider->subdivide($cell);

        $this->assertEmpty($children);
        $this->assertFalse($subdivider->canSubdivide($cell));
    }

    public function test_children_cover_parent_area(): void
    {
        $subdivider = new CellSubdivider(minRadius: 100, maxDepth: 4);

        $cell = ['lat' => -22.90, 'lng' => -43.30, 'radius' => 500, 'depth' => 0];
        $children = $subdivider->subdivide($cell);

        $lats = array_column($children, 'lat');
        $lngs = array_column($children, 'lng');

        $this->assertCount(2, array_unique($lats));
        $this->assertCount(2, array_unique($lngs));
    }
}

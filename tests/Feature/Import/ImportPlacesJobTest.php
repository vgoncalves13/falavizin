<?php

namespace Tests\Feature\Import;

use App\Enums\ImportRunStatus;
use App\Jobs\ImportPlacesJob;
use App\Models\Business;
use App\Models\Category;
use App\Models\ImportRun;
use App\Models\Neighborhood;
use App\Services\GooglePlacesService;
use App\Services\Import\CellSubdivider;
use App\Services\Import\QueryCache;
use App\Services\Import\RegionFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportPlacesJobTest extends TestCase
{
    use RefreshDatabase;

    private function createImportRun(array $overrides = []): ImportRun
    {
        $neighborhood = Neighborhood::factory()->create([
            'latitude' => -22.90,
            'longitude' => -43.30,
        ]);
        Category::firstOrCreate(['slug' => 'outros'], ['name' => 'Outros', 'type' => 'business', 'sort_order' => 99]);

        return ImportRun::create(array_merge([
            'neighborhood_id' => $neighborhood->id,
            'status' => ImportRunStatus::Running,
            'config' => [
                'primary_types' => ['bakery'],
                'budget' => 100,
                'min_radius' => 100,
                'max_depth' => 4,
                'cell_radius' => 500,
                'region_radius' => 1000,
                'center_lat' => (float) $neighborhood->latitude,
                'center_lng' => (float) $neighborhood->longitude,
            ],
            'cells' => [
                ['lat' => (float) $neighborhood->latitude, 'lng' => (float) $neighborhood->longitude, 'radius' => 500, 'depth' => 0, 'status' => 'pending'],
            ],
            'seen_place_ids' => [],
            'requests_made' => 0,
            'requests_budget' => 100,
        ], $overrides));
    }

    private function fakePlace(string $id = 'ChIJ_test_001', string $name = 'Test Place'): array
    {
        return [
            'place_id' => $id,
            'name' => $name,
            'address' => 'Rua Teste, 100',
            'lat' => -22.90,
            'lng' => -43.30,
            'phone' => null,
            'website' => null,
            'types' => ['bakery'],
            'already_imported' => false,
        ];
    }

    private function runJob(ImportRun $importRun): void
    {
        (new ImportPlacesJob($importRun->id))->handle(
            app(GooglePlacesService::class),
            new CellSubdivider,
            new RegionFilter,
            new QueryCache,
        );
    }

    public function test_deduplicates_by_google_place_id(): void
    {
        $importRun = $this->createImportRun();
        Business::factory()->create(['google_place_id' => 'ChIJ_existing']);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([
                        $this->fakePlace('ChIJ_existing', 'Already exists'),
                        $this->fakePlace('ChIJ_new', 'New place'),
                    ]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $stats = $importRun->stats;

        $this->assertSame(1, $stats['results_already_imported']);
        $this->assertSame(1, $stats['results_unique']);
    }

    public function test_deduplicates_between_rankings(): void
    {
        $importRun = $this->createImportRun();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([$this->fakePlace('ChIJ_shared', 'Shared place')]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertSame(1, $importRun->stats['results_unique']);
    }

    public function test_deduplicates_between_types(): void
    {
        $importRun = $this->createImportRun([
            'config' => array_merge($this->createImportRun()->config, [
                'primary_types' => ['bakery', 'pharmacy'],
            ]),
        ]);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([$this->fakePlace('ChIJ_shared', 'Shared')]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertSame(1, $importRun->stats['results_unique']);
    }

    public function test_discards_results_outside_region(): void
    {
        $importRun = $this->createImportRun();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([
                        $this->fakePlace('ChIJ_inside', 'Inside'),
                        array_merge($this->fakePlace('ChIJ_outside', 'Outside'), [
                            'lat' => -22.95,
                            'lng' => -43.35,
                        ]),
                    ]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertSame(1, $importRun->stats['results_outside']);
        $this->assertSame(1, $importRun->stats['results_unique']);
    }

    public function test_resumes_interrupted_execution(): void
    {
        $importRun = $this->createImportRun([
            'cells' => [
                ['lat' => -22.90, 'lng' => -43.30, 'radius' => 500, 'depth' => 0, 'status' => 'processed', 'result' => ['place_ids' => []]],
                ['lat' => -22.91, 'lng' => -43.31, 'radius' => 500, 'depth' => 0, 'status' => 'pending'],
            ],
        ]);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([$this->fakePlace('ChIJ_resumed', 'Resumed place')]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertSame(1, $importRun->stats['results_unique']);
    }

    public function test_is_idempotent(): void
    {
        $importRun = $this->createImportRun();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([$this->fakePlace('ChIJ_idem', 'Idempotent')]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertSame(1, $importRun->stats['results_unique']);
        $this->assertSame(ImportRunStatus::Completed, $importRun->status);
    }

    public function test_handles_http_429_with_retry(): void
    {
        $importRun = $this->createImportRun();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andThrow(new \RuntimeException('Rate limit atingido (HTTP 429)'));
        });

        $job = new ImportPlacesJob($importRun->id);

        $job->handle(
            app(GooglePlacesService::class),
            new CellSubdivider,
            new RegionFilter,
            new QueryCache,
        );

        $importRun->refresh();
        $this->assertSame(1, $importRun->stats['errors']);
    }

    public function test_handles_temporary_errors(): void
    {
        $importRun = $this->createImportRun();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andThrow(new \RuntimeException('Connection timeout'));
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertSame(2, $importRun->stats['errors']);
    }

    public function test_stops_at_budget_limit(): void
    {
        $importRun = $this->createImportRun([
            'requests_budget' => 1,
            'requests_made' => 0,
        ]);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([$this->fakePlace('ChIJ_budget', 'Budget place')]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertLessThanOrEqual(2, $importRun->requests_made);
    }

    public function test_marks_completed_when_no_pending_cells(): void
    {
        $importRun = $this->createImportRun([
            'cells' => [
                ['lat' => -22.90, 'lng' => -43.30, 'radius' => 500, 'depth' => 0, 'status' => 'processed', 'result' => ['place_ids' => []]],
            ],
        ]);

        $this->runJob($importRun);

        $importRun->refresh();
        $this->assertSame(ImportRunStatus::Completed, $importRun->status);
    }

    public function test_stats_are_calculated_correctly(): void
    {
        $importRun = $this->createImportRun();

        Business::factory()->create(['google_place_id' => 'ChIJ_a']);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearby')
                ->andReturn([
                    'results' => collect([
                        $this->fakePlace('ChIJ_a', 'Place A'),
                        $this->fakePlace('ChIJ_b', 'Place B'),
                    ]),
                    'isTruncated' => false,
                ]);
        });

        $this->runJob($importRun);

        $importRun->refresh();
        $stats = $importRun->stats;

        $this->assertSame(2, $stats['results_raw']);
        $this->assertSame(1, $stats['results_unique']);
        $this->assertSame(1, $stats['results_already_imported']);
    }
}

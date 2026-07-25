<?php

namespace App\Jobs;

use App\Enums\ImportRunStatus;
use App\Models\Business;
use App\Models\ImportRun;
use App\Services\GooglePlacesService;
use App\Services\Import\CellSubdivider;
use App\Services\Import\QueryCache;
use App\Services\Import\RegionFilter;
use App\Services\Import\RequestBudget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportPlacesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [30, 60, 120, 300];

    public int $timeout = 120;

    public function __construct(
        public int $importRunId,
    ) {}

    public function handle(
        GooglePlacesService $service,
        CellSubdivider $subdivider,
        RegionFilter $regionFilter,
        QueryCache $queryCache,
    ): void {
        $importRun = ImportRun::find($this->importRunId);

        if (! $importRun || $importRun->status !== ImportRunStatus::Running) {
            return;
        }

        $budget = new RequestBudget($importRun);
        $config = $importRun->config;
        $primaryTypes = $config['primary_types'] ?? [];
        $centerLat = $config['center_lat'];
        $centerLng = $config['center_lng'];
        $regionRadius = $config['region_radius'] ?? 1000;

        $stats = $importRun->stats ?? [
            'results_raw' => 0,
            'results_unique' => 0,
            'results_outside' => 0,
            'results_duplicate' => 0,
            'results_already_imported' => 0,
            'queries_cached' => 0,
            'errors' => 0,
        ];

        $cellIndex = $importRun->nextPendingCellIndex();

        if ($cellIndex === null) {
            $importRun->markCompleted($stats);

            return;
        }

        $cell = $importRun->cells[$cellIndex];
        $newPlaceIds = [];
        $newCells = [];
        $rankPreferences = ['DISTANCE', 'POPULARITY'];

        foreach ($primaryTypes as $type) {
            if (! $budget->canMakeRequest()) {
                break;
            }

            if ($importRun->fresh()->status !== ImportRunStatus::Running) {
                return;
            }

            foreach ($rankPreferences as $rank) {
                if (! $budget->canMakeRequest()) {
                    break;
                }

                $cacheKey = $queryCache->key(
                    $cell['lat'], $cell['lng'], $cell['radius'],
                    $type, $rank,
                );

                if ($queryCache->has($cacheKey)) {
                    $stats['queries_cached']++;

                    continue;
                }

                try {
                    $response = $service->searchNearby(
                        lat: $cell['lat'],
                        lng: $cell['lng'],
                        radius: $cell['radius'],
                        includedPrimaryTypes: [$type],
                        rankPreference: $rank,
                        maxResults: 20,
                    );

                    $budget->consume();
                    $importRun->incrementRequests();

                    $queryCache->remember($cacheKey, fn () => $response['results']);

                    $results = $response['results'];
                    $isTruncated = $response['isTruncated'];

                    $stats['results_raw'] += $results->count();

                    $filteredResults = $regionFilter->filterPlaces(
                        $results->toArray(),
                        $centerLat,
                        $centerLng,
                        $regionRadius,
                    );

                    $stats['results_outside'] += $results->count() - count($filteredResults);

                    foreach ($filteredResults as $place) {
                        $placeId = $place['place_id'];

                        if ($importRun->hasSeen($placeId)) {
                            $stats['results_duplicate']++;

                            continue;
                        }

                        if (Business::where('google_place_id', $placeId)->exists()) {
                            $importRun->addSeenPlaceIds([$placeId]);
                            $stats['results_duplicate']++;
                            $stats['results_already_imported']++;

                            continue;
                        }

                        $importRun->addSeenPlaceIds([$placeId]);
                        $newPlaceIds[] = $placeId;
                        $stats['results_unique']++;

                        $importRun->refresh();
                    }

                    if ($isTruncated && $rank === 'DISTANCE' && $subdivider->canSubdivide($cell)) {
                        $children = $subdivider->subdivide($cell);
                        $newCells = array_merge($newCells, $children);
                        $importRun->markCellSaturated($cellIndex);
                    }

                    if ($rank === 'POPULARITY' || ! $isTruncated) {
                        break;
                    }

                } catch (\RuntimeException $e) {
                    $stats['errors']++;

                    if (str_contains($e->getMessage(), '429')) {
                        Log::warning('ImportPlacesJob: Rate limit atingido, aguardando...');
                        $this->release(30);

                        $importRun->stats = $stats;
                        $importRun->saveQuietly();

                        return;
                    }

                    Log::error('ImportPlacesJob: Erro na busca', [
                        'cell' => $cell,
                        'type' => $type,
                        'rank' => $rank,
                        'error' => $e->getMessage(),
                    ]);
                }

                if (! $budget->canMakeRequest()) {
                    break;
                }
            }

            if (! $budget->canMakeRequest()) {
                break;
            }
        }

        if (($importRun->cells[$cellIndex]['status'] ?? 'pending') === 'pending') {
            $importRun->markCellProcessed($cellIndex, [
                'place_ids' => $newPlaceIds,
                'truncated' => ($response['isTruncated'] ?? false),
            ]);
        }

        if (! empty($newCells)) {
            $importRun->addCells($newCells);
        }

        $importRun->stats = $stats;
        $importRun->saveQuietly();

        if ($importRun->nextPendingCellIndex() !== null && $budget->canMakeRequest()) {
            self::dispatch($this->importRunId)->delay(now()->addMilliseconds(200));
        } else {
            $importRun->refresh();

            if ($importRun->status === ImportRunStatus::Running) {
                $finalStats = $importRun->statsSnapshot();
                $importRun->markCompleted($finalStats);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        $importRun = ImportRun::find($this->importRunId);

        if ($importRun && $importRun->status->isActive()) {
            $importRun->markFailed($exception->getMessage());
        }
    }
}

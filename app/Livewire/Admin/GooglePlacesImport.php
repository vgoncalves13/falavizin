<?php

namespace App\Livewire\Admin;

use App\Actions\ImportBusinessFromGoogleAction;
use App\Actions\StartImportAction;
use App\Enums\ImportRunStatus;
use App\Jobs\EnrichBusinessFromGoogle;
use App\Models\Business;
use App\Models\Category;
use App\Models\ImportRun;
use App\Models\Neighborhood;
use App\Services\GooglePlacesService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class GooglePlacesImport extends Component
{
    private const NON_BUSINESS_TYPES = [
        'locality', 'sublocality', 'sublocality_level_1', 'sublocality_level_2',
        'neighborhood', 'political', 'natural_feature', 'route', 'street_address',
        'postal_code', 'administrative_area_level_1', 'administrative_area_level_2',
        'administrative_area_level_3', 'country', 'colloquial_area',
        'apartment_building', 'apartment_complex', 'condominium_complex', 'housing_complex',
        'place_of_worship', 'church', 'hindu_temple', 'mosque', 'synagogue',
        'city_park', 'park', 'playground', 'town_square',
    ];

    public int $neighborhoodId = 0;

    public float $lat = 0;

    public float $lng = 0;

    public int $radius = 1000;

    public int $categoryId = 0;

    public int $maxResults = 20;

    /** @var array<int, array<string, mixed>> */
    public array $results = [];

    /** @var array<int, string> */
    public array $selected = [];

    public bool $importing = false;

    public bool $searched = false;

    /** @var Collection<int, Neighborhood> */
    public $neighborhoods;

    // Advanced import properties
    public int $budget = 200;

    public int $minRadius = 100;

    public int $maxDepth = 4;

    public ?int $importRunId = null;

    public ?array $importStats = null;

    public string $importStatus = 'idle';

    public function mount(): void
    {
        $this->neighborhoods = Neighborhood::active()->orderBy('name')->get();
        $this->checkActiveImport();
    }

    public function updatedNeighborhoodId(int $value): void
    {
        $neighborhood = $this->neighborhoods->firstWhere('id', $value);

        if ($neighborhood) {
            $this->lat = (float) $neighborhood->latitude;
            $this->lng = (float) $neighborhood->longitude;
        }
    }

    public function search(): void
    {
        $this->validate([
            'neighborhoodId' => ['required', 'integer', 'min:1'],
            'categoryId' => ['nullable', 'integer', 'min:0'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:100', 'max:50000'],
            'maxResults' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $results = app(GooglePlacesService::class)->searchNearbySimple(
            lat: $this->lat,
            lng: $this->lng,
            radius: $this->radius,
            maxResults: $this->maxResults,
        );

        $placeIds = $results->pluck('place_id')->filter()->all();
        $existing = Business::whereIn('google_place_id', $placeIds)
            ->pluck('google_place_id')
            ->all();

        $this->results = $results->map(function (array $place) use ($existing) {
            $place['already_imported'] = in_array($place['place_id'], $existing);
            $place['is_likely_business'] = $this->isLikelyBusiness($place['types']);

            return $place;
        })->all();

        $this->selected = [];
        $this->searched = true;
    }

    public function selectAll(): void
    {
        $this->selected = collect($this->results)
            ->filter(fn (array $p) => ! $p['already_imported'] && $p['is_likely_business'])
            ->pluck('place_id')
            ->all();
    }

    private function isLikelyBusiness(array $types): bool
    {
        return empty(array_intersect($types, self::NON_BUSINESS_TYPES));
    }

    public function import(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $neighborhood = Neighborhood::active()->findOrFail($this->neighborhoodId);
        $action = app(ImportBusinessFromGoogleAction::class);
        $imported = 0;

        foreach ($this->results as $place) {
            if (! in_array($place['place_id'], $this->selected)) {
                continue;
            }

            $business = $action->execute($place, $neighborhood, $this->categoryId);

            if ($business !== null) {
                EnrichBusinessFromGoogle::dispatch($business->id)
                    ->delay(now()->addSeconds($imported * 3));
                $imported++;
            }
        }

        $this->selected = [];

        $placeIds = collect($this->results)->pluck('place_id')->filter()->all();
        $existing = Business::whereIn('google_place_id', $placeIds)->pluck('google_place_id')->all();

        $this->results = collect($this->results)->map(function (array $place) use ($existing) {
            $place['already_imported'] = in_array($place['place_id'], $existing);

            return $place;
        })->all();

        session()->flash('success', "{$imported} negócio(s) importado(s) com sucesso.");
    }

    public function startImport(): void
    {
        $this->validate([
            'neighborhoodId' => ['required', 'integer', 'min:1'],
            'budget' => ['required', 'integer', 'min:10', 'max:10000'],
            'minRadius' => ['required', 'integer', 'min:50', 'max:1000'],
            'maxDepth' => ['required', 'integer', 'min:1', 'max:6'],
            'radius' => ['required', 'integer', 'min:100', 'max:50000'],
        ]);

        $neighborhood = Neighborhood::active()->findOrFail($this->neighborhoodId);

        $importRun = app(StartImportAction::class)->execute($neighborhood, [
            'budget' => $this->budget,
            'min_radius' => $this->minRadius,
            'max_depth' => $this->maxDepth,
            'region_radius' => $this->radius,
        ]);

        $this->importRunId = $importRun->id;
        $this->importStatus = 'running';
        $this->importStats = $importRun->statsSnapshot();
    }

    public function pollProgress(): void
    {
        if ($this->importRunId === null) {
            return;
        }

        $importRun = ImportRun::find($this->importRunId);

        if (! $importRun) {
            $this->importStatus = 'idle';
            $this->importRunId = null;

            return;
        }

        $this->importStatus = match ($importRun->status) {
            ImportRunStatus::Running => 'running',
            ImportRunStatus::Completed => 'completed',
            ImportRunStatus::Cancelled => 'cancelled',
            ImportRunStatus::Failed => 'failed',
            default => 'idle',
        };

        $this->importStats = $importRun->statsSnapshot();

        if ($importRun->status === ImportRunStatus::Completed) {
            $this->loadImportResults($importRun);
        }
    }

    public function cancelImport(): void
    {
        if ($this->importRunId === null) {
            return;
        }

        $importRun = ImportRun::find($this->importRunId);

        if ($importRun && $importRun->status->isActive()) {
            $importRun->markCancelled();
            $this->importStatus = 'cancelled';
        }
    }

    private function loadImportResults(ImportRun $importRun): void
    {
        $config = $importRun->config;
        $neighborhood = $importRun->neighborhood;

        if (! $neighborhood) {
            return;
        }

        $allPlaceIds = [];
        foreach ($importRun->cells ?? [] as $cell) {
            if (isset($cell['result']['place_ids'])) {
                $allPlaceIds = array_merge($allPlaceIds, $cell['result']['place_ids']);
            }
        }

        $allPlaceIds = array_unique(array_merge($allPlaceIds, $importRun->seen_place_ids ?? []));

        if (empty($allPlaceIds)) {
            return;
        }

        $existing = Business::whereIn('google_place_id', $allPlaceIds)
            ->pluck('google_place_id')
            ->all();

        $newPlaceIds = array_values(array_diff($allPlaceIds, $existing));

        $this->results = array_map(fn (string $id) => [
            'place_id' => $id,
            'name' => '(encontrado na importação completa)',
            'address' => null,
            'lat' => null,
            'lng' => null,
            'phone' => null,
            'website' => null,
            'types' => [],
            'already_imported' => false,
            'is_likely_business' => true,
        ], $newPlaceIds);

        $this->searched = true;
    }

    private function checkActiveImport(): void
    {
        $activeRun = ImportRun::query()
            ->where('status', ImportRunStatus::Running)
            ->latest()
            ->first();

        if ($activeRun) {
            $this->importRunId = $activeRun->id;
            $this->importStatus = 'running';
            $this->importStats = $activeRun->statsSnapshot();
            $this->neighborhoodId = $activeRun->neighborhood_id;
        }
    }

    public function render()
    {
        $categories = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.admin.google-places-import', compact('categories'))
            ->layout('layouts.app')
            ->layoutData(['title' => 'Importar Google Places']);
    }
}

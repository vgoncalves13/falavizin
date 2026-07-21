<?php

namespace App\Livewire\Admin;

use App\Actions\ImportBusinessFromGoogleAction;
use App\Jobs\EnrichBusinessFromGoogle;
use App\Models\Business;
use App\Models\Category;
use App\Models\Setting;
use App\Services\GooglePlacesService;
use Livewire\Component;

class GooglePlacesImport extends Component
{
    private const NON_BUSINESS_TYPES = [
        'locality', 'sublocality', 'sublocality_level_1', 'sublocality_level_2',
        'neighborhood', 'political', 'natural_feature', 'route', 'street_address',
        'postal_code', 'administrative_area_level_1', 'administrative_area_level_2',
        'administrative_area_level_3', 'country', 'colloquial_area',
    ];

    public float $lat = 0;

    public float $lng = 0;

    public int $radius = 1000;

    public string $neighborhood = '';

    public int $categoryId = 0;

    public int $maxResults = 20;

    public function mount(): void
    {
        $this->lat = (float) Setting::get('neighborhood_lat', 0);
        $this->lng = (float) Setting::get('neighborhood_lng', 0);
        $this->neighborhood = Setting::get('neighborhood_name', '');
    }

    /** @var array<int, array<string, mixed>> */
    public array $results = [];

    /** @var array<int, string> */
    public array $selected = [];

    public bool $importing = false;

    public bool $searched = false;

    public function search(): void
    {
        $this->validate([
            'neighborhood' => ['required', 'string', 'max:100'],
            'categoryId' => ['nullable', 'integer', 'min:0'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:100', 'max:50000'],
            'maxResults' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $results = app(GooglePlacesService::class)->searchNearby(
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

        $action = app(ImportBusinessFromGoogleAction::class);
        $categoryId = $this->categoryId ?: Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->value('id');
        $imported = 0;

        foreach ($this->results as $place) {
            if (! in_array($place['place_id'], $this->selected)) {
                continue;
            }

            $business = $action->execute($place, $this->neighborhood, $categoryId);

            if ($business !== null) {
                EnrichBusinessFromGoogle::dispatch($business->id)
                    ->delay(now()->addSeconds($imported * 3));
                $imported++;
            }
        }

        $this->selected = [];

        // Re-mark already imported
        $placeIds = collect($this->results)->pluck('place_id')->filter()->all();
        $existing = Business::whereIn('google_place_id', $placeIds)->pluck('google_place_id')->all();

        $this->results = collect($this->results)->map(function (array $place) use ($existing) {
            $place['already_imported'] = in_array($place['place_id'], $existing);

            return $place;
        })->all();

        session()->flash('success', "{$imported} negócio(s) importado(s) com sucesso.");
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

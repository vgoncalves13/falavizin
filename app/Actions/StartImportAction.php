<?php

namespace App\Actions;

use App\Enums\ImportRunStatus;
use App\Jobs\ImportPlacesJob;
use App\Models\ImportRun;
use App\Models\Neighborhood;
use App\Services\Import\GridGenerator;

class StartImportAction
{
    public const DEFAULT_PRIMARY_TYPES = [
        'restaurant', 'bakery', 'pharmacy', 'supermarket', 'barber_shop',
        'beauty_salon', 'gym', 'pet_store', 'car_repair', 'clothing_store',
        'bank', 'dentist', 'doctor', 'lawyer', 'laundry', 'florist',
        'electronics_store', 'furniture_store', 'hardware_store',
        'convenience_store', 'bar', 'cafe', 'night_club', 'school',
        'real_estate_agency', 'insurance_agency', 'accounting',
        'veterinary_care', 'physiotherapist', 'optician',
    ];

    public function execute(
        Neighborhood $neighborhood,
        array $config = [],
    ): ImportRun {
        $primaryTypes = $config['primary_types'] ?? self::DEFAULT_PRIMARY_TYPES;
        $budget = $config['budget'] ?? 200;
        $minRadius = $config['min_radius'] ?? 100;
        $maxDepth = $config['max_depth'] ?? 4;
        $cellRadius = $config['cell_radius'] ?? null;

        $lat = (float) $neighborhood->latitude;
        $lng = (float) $neighborhood->longitude;
        $regionRadius = $config['region_radius'] ?? 1000;

        if ($cellRadius === null) {
            $cellRadius = max($regionRadius / 2, 200);
        }

        $gridGenerator = new GridGenerator;
        $cells = $gridGenerator->generate($lat, $lng, $regionRadius, $cellRadius);

        $importRun = ImportRun::create([
            'neighborhood_id' => $neighborhood->id,
            'status' => ImportRunStatus::Pending,
            'mode' => 'complete',
            'config' => [
                'primary_types' => $primaryTypes,
                'budget' => $budget,
                'min_radius' => $minRadius,
                'max_depth' => $maxDepth,
                'cell_radius' => $cellRadius,
                'region_radius' => $regionRadius,
                'center_lat' => $lat,
                'center_lng' => $lng,
            ],
            'cells' => $cells,
            'seen_place_ids' => [],
            'requests_made' => 0,
            'requests_budget' => $budget,
        ]);

        $importRun->markRunning();

        ImportPlacesJob::dispatch($importRun->id);

        return $importRun;
    }
}

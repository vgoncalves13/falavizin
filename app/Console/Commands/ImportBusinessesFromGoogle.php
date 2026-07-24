<?php

namespace App\Console\Commands;

use App\Actions\ImportBusinessFromGoogleAction;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;

class ImportBusinessesFromGoogle extends Command
{
    protected $signature = 'businesses:import-google
                            {--neighborhood= : ID do bairro para associar os negócios importados}
                            {--lat= : Latitude do centro da busca (sobrepõe coordenadas do bairro)}
                            {--lng= : Longitude do centro da busca (sobrepõe coordenadas do bairro)}
                            {--radius=1000 : Raio em metros}
                            {--limit=20 : Máximo de resultados}';

    protected $description = 'Importa negócios da Google Places API (Nearby Search) para o banco de dados';

    public function handle(GooglePlacesService $service, ImportBusinessFromGoogleAction $action): int
    {
        $neighborhoodId = $this->option('neighborhood');
        $lat = $this->option('lat');
        $lng = $this->option('lng');
        $radius = (int) $this->option('radius');
        $limit = (int) $this->option('limit');

        if (! $neighborhoodId) {
            $this->error('Informe o ID do bairro com --neighborhood=ID');

            return Command::FAILURE;
        }

        $neighborhood = Neighborhood::active()->find($neighborhoodId);

        if (! $neighborhood) {
            $this->error("Bairro com ID {$neighborhoodId} não encontrado ou inativo.");

            return Command::FAILURE;
        }

        $lat = $lat ?: $neighborhood->latitude;
        $lng = $lng ?: $neighborhood->longitude;

        if (! $lat || ! $lng) {
            $this->error('Bairro sem coordenadas. Informe --lat e --lng manualmente.');

            return Command::FAILURE;
        }

        if (! config('services.rapidapi.key')) {
            $this->error('RAPIDAPI_KEY não configurada no .env');

            return Command::FAILURE;
        }

        $hasCategories = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->exists();

        if (! $hasCategories) {
            $this->error('Nenhuma categoria de negócio encontrada. Execute o seeder primeiro.');

            return Command::FAILURE;
        }

        $this->info("Buscando negócios próximos a {$neighborhood->name} ({$lat}, {$lng}) com raio de {$radius}m...");

        try {
            $response = $service->searchNearby(
                lat: (float) $lat,
                lng: (float) $lng,
                radius: $radius,
                maxResults: $limit,
            );
            $results = $response['results'];
        } catch (\RuntimeException $e) {
            $this->error('Falha na requisição: '.$e->getMessage());

            return Command::FAILURE;
        }

        if ($results->isEmpty()) {
            $this->warn('Nenhum resultado encontrado.');

            return Command::SUCCESS;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($results as $place) {
            $business = $action->execute($place, $neighborhood, 0);

            if ($business !== null) {
                $imported++;
                $this->line("  ✓ {$place['name']}");
            } else {
                $skipped++;
                $this->line("  — {$place['name']} (já importado)");
            }
        }

        $this->info("Importados: {$imported} | Ignorados (duplicatas): {$skipped}");

        return Command::SUCCESS;
    }
}

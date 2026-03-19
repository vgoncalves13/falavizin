<?php

namespace App\Console\Commands;

use App\Actions\ImportBusinessFromGoogleAction;
use App\Models\Category;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;

class ImportBusinessesFromGoogle extends Command
{
    protected $signature = 'businesses:import-google
                            {--neighborhood= : Nome do bairro para label dos negócios importados}
                            {--lat= : Latitude do centro da busca}
                            {--lng= : Longitude do centro da busca}
                            {--radius=1000 : Raio em metros}
                            {--limit=20 : Máximo de resultados}';

    protected $description = 'Importa negócios da Google Places API (Nearby Search) para o banco de dados';

    public function handle(GooglePlacesService $service, ImportBusinessFromGoogleAction $action): int
    {
        $neighborhood = $this->option('neighborhood');
        $lat = $this->option('lat');
        $lng = $this->option('lng');
        $radius = (int) $this->option('radius');
        $limit = (int) $this->option('limit');

        if (! $neighborhood) {
            $this->error('Informe o bairro com --neighborhood="Nome do Bairro"');

            return Command::FAILURE;
        }

        if (! $lat || ! $lng) {
            $this->error('Informe as coordenadas com --lat e --lng');

            return Command::FAILURE;
        }

        if (! config('services.rapidapi.key')) {
            $this->error('RAPIDAPI_KEY não configurada no .env');

            return Command::FAILURE;
        }

        $defaultCategory = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->first();

        if (! $defaultCategory) {
            $this->error('Nenhuma categoria de negócio encontrada. Execute o seeder primeiro.');

            return Command::FAILURE;
        }

        $this->info("Buscando negócios próximos a ({$lat}, {$lng}) com raio de {$radius}m...");

        try {
            $results = $service->searchNearby(
                lat: (float) $lat,
                lng: (float) $lng,
                radius: $radius,
                maxResults: $limit,
            );
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
            $business = $action->execute($place, $neighborhood, $defaultCategory->id);

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

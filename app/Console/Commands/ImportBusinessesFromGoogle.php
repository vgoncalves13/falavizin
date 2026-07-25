<?php

namespace App\Console\Commands;

use App\Actions\ImportBusinessFromGoogleAction;
use App\Actions\StartImportAction;
use App\Enums\ImportRunStatus;
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
                            {--limit=20 : Máximo de resultados}
                            {--advanced : Usar importação avançada com grade e tipos}
                            {--budget=200 : Limite de requisições (modo avançado)}
                            {--min-radius=100 : Raio mínimo em metros (modo avançado)}
                            {--max-depth=4 : Profundidade máxima de subdivisão (modo avançado)}';

    protected $description = 'Importa negócios da Google Places API para o banco de dados';

    public function handle(GooglePlacesService $service, ImportBusinessFromGoogleAction $action): int
    {
        $neighborhoodId = $this->option('neighborhood');

        if (! $neighborhoodId) {
            $this->error('Informe o ID do bairro com --neighborhood=ID');

            return Command::FAILURE;
        }

        $neighborhood = Neighborhood::active()->find($neighborhoodId);

        if (! $neighborhood) {
            $this->error("Bairro com ID {$neighborhoodId} não encontrado ou inativo.");

            return Command::FAILURE;
        }

        if (! $neighborhood->latitude || ! $neighborhood->longitude) {
            $lat = $this->option('lat');
            $lng = $this->option('lng');

            if (! $lat || ! $lng) {
                $this->error('Bairro sem coordenadas. Informe --lat e --lng manualmente.');

                return Command::FAILURE;
            }
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

        if ($this->option('advanced')) {
            return $this->runAdvancedImport($neighborhood);
        }

        return $this->runSimpleImport($service, $action, $neighborhood);
    }

    private function runSimpleImport(GooglePlacesService $service, ImportBusinessFromGoogleAction $action, Neighborhood $neighborhood): int
    {
        $lat = $this->option('lat') ?: $neighborhood->latitude;
        $lng = $this->option('lng') ?: $neighborhood->longitude;
        $radius = (int) $this->option('radius');
        $limit = (int) $this->option('limit');

        $this->info("Buscando negócios próximos a {$neighborhood->name} ({$lat}, {$lng}) com raio de {$radius}m...");

        try {
            $results = $service->searchNearbySimple(
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

    private function runAdvancedImport(Neighborhood $neighborhood): int
    {
        $budget = (int) $this->option('budget');
        $minRadius = (int) $this->option('min-radius');
        $maxDepth = (int) $this->option('max-depth');
        $radius = (int) $this->option('radius');

        $this->info("Iniciando importação avançada para {$neighborhood->name}...");
        $this->info("  Budget: {$budget} requisições | Raio: {$radius}m | Raio mínimo: {$minRadius}m | Profundidade: {$maxDepth}");

        $importRun = app(StartImportAction::class)->execute($neighborhood, [
            'budget' => $budget,
            'min_radius' => $minRadius,
            'max_depth' => $maxDepth,
            'region_radius' => $radius,
        ]);

        $this->info("ImportRun #{$importRun->id} criado. Acompanhe o progresso no painel admin.");

        $bar = $this->output->createProgressBar();
        $bar->setFormat(' %current%/%max% [%bar%] %percent%% — %message%');
        $bar->setMessage('Processando...');
        $bar->start();

        while ($importRun->status === ImportRunStatus::Running) {
            $importRun->refresh();
            $stats = $importRun->statsSnapshot();

            $bar->setProgress(($stats['cells_processed'] ?? 0) + ($stats['cells_saturated'] ?? 0));
            $bar->setMax($stats['cells_total'] ?? 1);
            $unique = $stats['results_unique'] ?? 0;
            $bar->setMessage("Requisições: {$stats['requests_made']}/{$stats['requests_budget']} | Únicos: {$unique}");

            sleep(2);
        }

        $bar->finish();
        $this->newLine();

        $importRun->refresh();
        $stats = $importRun->statsSnapshot();

        $this->info('Importação concluída!');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Requisições feitas', $stats['requests_made'] ?? 0],
                ['Resultados brutos', $stats['results_raw'] ?? 0],
                ['Únicos', $stats['results_unique'] ?? 0],
                ['Duplicados', $stats['results_duplicate'] ?? 0],
                ['Fora da região', $stats['results_outside'] ?? 0],
                ['Já cadastrados', $stats['results_already_imported'] ?? 0],
                ['Células saturadas', $stats['cells_saturated'] ?? 0],
                ['Erros', $stats['errors'] ?? 0],
            ]
        );

        return $importRun->status === ImportRunStatus::Completed
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}

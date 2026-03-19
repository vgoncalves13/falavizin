<?php

namespace App\Console\Commands;

use App\Jobs\EnrichBusinessFromGoogle;
use App\Models\Business;
use Illuminate\Console\Command;

class EnrichBusinessesFromGoogle extends Command
{
    protected $signature = 'businesses:enrich-google
                            {--id= : ID de um negócio específico}
                            {--sync : Processar de forma síncrona (sem fila)}';

    protected $description = 'Enriquece negócios importados do Google Places com detalhes (telefone, site, horários e foto de capa)';

    public function handle(): int
    {
        if (! config('services.rapidapi.key')) {
            $this->error('RAPIDAPI_KEY não configurada no .env');

            return Command::FAILURE;
        }

        $query = Business::query()->whereNotNull('google_place_id');

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $businesses = $query->get();

        if ($businesses->isEmpty()) {
            $this->warn('Nenhum negócio com google_place_id encontrado.');

            return Command::SUCCESS;
        }

        $sync = $this->option('sync');
        $this->info(($sync ? 'Processando' : 'Enfileirando')." {$businesses->count()} negócio(s)...");

        foreach ($businesses as $i => $business) {
            if ($sync) {
                if ($i > 0) {
                    sleep(2);
                }

                try {
                    EnrichBusinessFromGoogle::dispatchSync($business->id);
                    $this->line("  ✓ [{$business->id}] {$business->name}");
                } catch (\Throwable $e) {
                    $this->warn("  ✗ [{$business->id}] {$business->name}: ".$e->getMessage());
                }
            } else {
                EnrichBusinessFromGoogle::dispatch($business->id)->delay(now()->addSeconds($i * 3));
                $this->line("  → [{$business->id}] {$business->name} (enfileirado)");
            }
        }

        $this->info('Concluído.');

        return Command::SUCCESS;
    }
}

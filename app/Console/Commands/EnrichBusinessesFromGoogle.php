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

        foreach ($businesses as $business) {
            if ($sync) {
                EnrichBusinessFromGoogle::dispatchSync($business->id);
                $this->line("  ✓ [{$business->id}] {$business->name}");
            } else {
                EnrichBusinessFromGoogle::dispatch($business->id);
                $this->line("  → [{$business->id}] {$business->name} (enfileirado)");
            }
        }

        $this->info('Concluído.');

        return Command::SUCCESS;
    }
}

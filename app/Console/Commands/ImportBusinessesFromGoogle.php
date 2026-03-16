<?php

namespace App\Console\Commands;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportBusinessesFromGoogle extends Command
{
    protected $signature = 'businesses:import-google
                            {--neighborhood= : Nome do bairro para buscar}
                            {--type= : Tipo de negócio (ex: restaurant, pharmacy)}
                            {--limit=20 : Máximo de resultados}';

    protected $description = 'Importa negócios da Google Places API para o banco de dados';

    public function handle(): int
    {
        $neighborhood = $this->option('neighborhood');
        $type = $this->option('type');
        $limit = (int) $this->option('limit');

        if (! $neighborhood) {
            $this->error('Informe o bairro com --neighborhood="Nome do Bairro"');

            return Command::FAILURE;
        }

        $apiKey = config('services.google.places_key');

        if (! $apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY não configurada no .env');

            return Command::FAILURE;
        }

        $query = trim(($type ? $type.' em ' : '').$neighborhood);

        $this->info("Buscando: \"{$query}\" na Google Places API...");

        $response = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
            'query' => $query,
            'language' => 'pt-BR',
            'key' => $apiKey,
        ]);

        if (! $response->successful()) {
            $this->error('Falha na requisição à API.');

            return Command::FAILURE;
        }

        $results = collect($response->json('results', []))->take($limit);

        if ($results->isEmpty()) {
            $this->warn('Nenhum resultado encontrado.');

            return Command::SUCCESS;
        }

        $defaultCategory = Category::query()->where('type', 'business')->orderBy('sort_order')->first();

        $imported = 0;
        $skipped = 0;

        foreach ($results as $place) {
            $placeId = $place['place_id'] ?? null;

            if (! $placeId) {
                continue;
            }

            if (Business::where('google_place_id', $placeId)->exists()) {
                $skipped++;

                continue;
            }

            Business::create([
                'category_id' => $defaultCategory?->id ?? 1,
                'name' => $place['name'],
                'neighborhood' => $neighborhood,
                'address' => $place['formatted_address'] ?? null,
                'lat' => $place['geometry']['location']['lat'] ?? null,
                'lng' => $place['geometry']['location']['lng'] ?? null,
                'google_place_id' => $placeId,
                'status' => BusinessStatus::Approved,
                'claimed' => false,
            ]);

            $imported++;
        }

        $this->info("Importados: {$imported} | Ignorados (duplicatas): {$skipped}");

        return Command::SUCCESS;
    }
}

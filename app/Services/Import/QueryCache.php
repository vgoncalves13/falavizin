<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Cache;

class QueryCache
{
    private const TTL = 3600;

    public function key(
        float $lat,
        float $lng,
        float $radius,
        string $type,
        string $rankPreference,
    ): string {
        $data = sprintf('%.7f|%.7f|%.1f|%s|%s', $lat, $lng, $radius, $type, $rankPreference);

        return 'places_query:'.md5($data);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function remember(string $key, callable $callback): mixed
    {
        return Cache::remember($key, self::TTL, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }
}

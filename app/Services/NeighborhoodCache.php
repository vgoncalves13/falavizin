<?php

namespace App\Services;

use App\Models\Neighborhood;
use Closure;
use Illuminate\Support\Facades\Cache;

final class NeighborhoodCache
{
    public const HOME_CATEGORIES = 'home:categories';

    public const HOME_POSTS = 'home:posts';

    public const HOME_BUSINESSES = 'home:featured_businesses';

    public const HOME_PROMOTIONS = 'home:promotions';

    public const HOME_EVENTS = 'home:upcoming_events';

    public const HOME_SPONSORED = 'home:sponsored_posts';

    public const HOME_REQUESTS = 'home:requests';

    public const HOME_PULSE_POSTS = 'home:pulso_posts';

    public const HOME_PULSE_RESOLVED = 'home:pulso_resolved';

    public const HOME_STATS = 'home:stats';

    public const NEIGHBORHOODS_ACTIVE = 'neighborhoods:active';

    public const PULSE_PREFIX = 'pulso';

    private const KEYS = [
        self::HOME_CATEGORIES,
        self::HOME_POSTS,
        self::HOME_BUSINESSES,
        self::HOME_PROMOTIONS,
        self::HOME_EVENTS,
        self::HOME_SPONSORED,
        self::HOME_REQUESTS,
        self::HOME_PULSE_POSTS,
        self::HOME_PULSE_RESOLVED,
        self::HOME_STATS,
    ];

    public function key(Neighborhood|int $neighborhood, string $key): string
    {
        $id = $neighborhood instanceof Neighborhood ? $neighborhood->getKey() : $neighborhood;

        return "neighborhood:{$id}:{$key}";
    }

    public function remember(Neighborhood|int $neighborhood, string $key, Closure $callback): mixed
    {
        return Cache::remember($this->key($neighborhood, $key), 300, $callback);
    }

    public function forget(Neighborhood|int $neighborhood): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($this->key($neighborhood, $key));
        }
    }

    public function forgetAll(): void
    {
        Neighborhood::query()->pluck('id')->each(fn (int $id) => $this->forget($id));
    }

    public function forgetActive(): void
    {
        Cache::forget(self::NEIGHBORHOODS_ACTIVE);
    }

    public static function keys(): array
    {
        return self::KEYS;
    }
}

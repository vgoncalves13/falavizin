<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HomeCache implements ShouldHandleEventsAfterCommit
{
    public const CATEGORIES = 'home:categories';

    public const FEATURED_BUSINESSES = 'home:featured_businesses';

    public const PROMOTIONS = 'home:promotions';

    public const UPCOMING_EVENTS = 'home:upcoming_events';

    public const SPONSORED_POSTS = 'home:sponsored_posts';

    public const POSTS = 'home:posts';

    public const REQUESTS = 'home:requests';

    public const PULSE_POSTS = 'home:pulso_posts';

    public const PULSE_RESOLVED = 'home:pulso_resolved';

    public const STATS = 'home:stats';

    private const KEYS = [
        self::CATEGORIES,
        self::FEATURED_BUSINESSES,
        self::PROMOTIONS,
        self::UPCOMING_EVENTS,
        self::SPONSORED_POSTS,
        self::POSTS,
        self::REQUESTS,
        self::PULSE_POSTS,
        self::PULSE_RESOLVED,
        self::STATS,
    ];

    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::remember($key, 300, $callback);
    }

    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
    }

    public function saved(Model $model): void
    {
        if ($model instanceof User && ! $model->wasRecentlyCreated) {
            return;
        }

        self::flush();
    }

    public function deleted(Model $model): void
    {
        self::flush();
    }

    public function restored(Model $model): void
    {
        self::flush();
    }
}

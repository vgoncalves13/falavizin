<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessAnalytics extends Model
{
    protected $fillable = [
        'business_id',
        'event_type',
        'recorded_date',
        'count',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public static function record(Business $business, string $eventType): void
    {
        $today = now()->toDateString();

        self::query()->updateOrCreate(
            [
                'business_id' => $business->id,
                'event_type' => $eventType,
                'recorded_date' => $today,
            ],
            [
                'count' => \DB::raw('count + 1'),
            ]
        );
    }

    public static function getStats(Business $business, int $days = 30): array
    {
        $startDate = now()->subDays($days)->toDateString();

        $stats = self::query()
            ->where('business_id', $business->id)
            ->where('recorded_date', '>=', $startDate)
            ->selectRaw('event_type, SUM(count) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        return [
            'views' => $stats->get('view', 0),
            'phone_clicks' => $stats->get('phone_click', 0),
            'whatsapp_clicks' => $stats->get('whatsapp_click', 0),
        ];
    }

    public static function getDailyStats(Business $business, int $days = 30): array
    {
        $startDate = now()->subDays($days)->toDateString();

        return self::query()
            ->where('business_id', $business->id)
            ->where('recorded_date', '>=', $startDate)
            ->selectRaw('recorded_date, event_type, SUM(count) as total')
            ->groupBy('recorded_date', 'event_type')
            ->orderBy('recorded_date')
            ->get()
            ->groupBy('recorded_date')
            ->map(fn ($day) => $day->pluck('total', 'event_type'))
            ->toArray();
    }
}

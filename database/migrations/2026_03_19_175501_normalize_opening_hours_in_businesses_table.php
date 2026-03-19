<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DAY_MAP = [
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo',
    ];

    public function up(): void
    {
        $businesses = DB::table('businesses')
            ->whereNotNull('opening_hours')
            ->get(['id', 'opening_hours']);

        foreach ($businesses as $business) {
            $hours = json_decode($business->opening_hours, true);

            // Skip if already in structured format (first element has a 'day' key)
            if (empty($hours) || ! is_string($hours[0])) {
                continue;
            }

            $structured = [];
            foreach ($hours as $line) {
                [$dayEn, $timeStr] = array_pad(explode(': ', $line, 2), 2, '');
                $day = self::DAY_MAP[$dayEn] ?? $dayEn;
                $timeStr = trim($timeStr);

                if (strtolower($timeStr) === 'closed') {
                    $structured[] = ['day' => $day, 'open' => '', 'close' => '', 'closed' => true];
                } elseif (strtolower($timeStr) === 'open 24 hours') {
                    $structured[] = ['day' => $day, 'open' => '00:00', 'close' => '23:59', 'closed' => false];
                } else {
                    [$openStr, $closeStr] = array_pad(explode(' – ', $timeStr, 2), 2, '');
                    $structured[] = [
                        'day' => $day,
                        'open' => self::to24h($openStr),
                        'close' => self::to24h($closeStr),
                        'closed' => false,
                    ];
                }
            }

            DB::table('businesses')
                ->where('id', $business->id)
                ->update(['opening_hours' => json_encode($structured)]);
        }
    }

    public function down(): void
    {
        // Irreversible data transformation — no rollback
    }

    private static function to24h(string $time): string
    {
        $time = trim($time);
        if (empty($time)) {
            return '';
        }

        $dt = DateTime::createFromFormat('g:i A', $time);

        return $dt ? $dt->format('H:i') : $time;
    }
};

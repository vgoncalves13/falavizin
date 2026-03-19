<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $businesses = DB::table('businesses')
            ->whereNotNull('opening_hours')
            ->get(['id', 'opening_hours']);

        foreach ($businesses as $business) {
            $hours = json_decode($business->opening_hours, true);

            if (empty($hours) || ! is_array($hours)) {
                continue;
            }

            $changed = false;
            foreach ($hours as &$row) {
                // Fix rows where close is empty but open contains the full time range
                if (! ($row['closed'] ?? true) && empty($row['close']) && ! empty($row['open'])) {
                    $parts = self::splitTimeRange($row['open']);
                    if ($parts) {
                        $row['open'] = self::to24h($parts[0]);
                        $row['close'] = self::to24h($parts[1]);
                        $changed = true;
                    }
                }
            }
            unset($row);

            if ($changed) {
                DB::table('businesses')
                    ->where('id', $business->id)
                    ->update(['opening_hours' => json_encode($hours)]);
            }
        }
    }

    public function down(): void {}

    /** @return array{string, string}|null */
    private static function splitTimeRange(string $range): ?array
    {
        // Google Places uses U+2009 (thin space) + U+2013 (en dash) as separator
        // Also handle regular " - " just in case
        $parts = preg_split('/\s*[\x{2013}\x{2014}]\s*/u', $range, 2);

        if (is_array($parts) && count($parts) === 2) {
            return [trim($parts[0]), trim($parts[1])];
        }

        return null;
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

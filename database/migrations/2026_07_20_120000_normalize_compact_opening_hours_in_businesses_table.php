<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('businesses')->whereNotNull('opening_hours')->orderBy('id')->each(function (object $business): void {
            $compactHours = json_decode($business->opening_hours, true);

            if (! is_array($compactHours) || array_is_list($compactHours)) {
                return;
            }

            $normalized = $this->normalize($compactHours);

            if ($normalized !== null) {
                DB::table('businesses')->where('id', $business->id)->update([
                    'opening_hours' => json_encode($normalized),
                ]);
            }
        }, 100);
    }

    public function down(): void {}

    /** @param array<string, string> $compactHours */
    private function normalize(array $compactHours): ?array
    {
        $days = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo'];
        $ranges = ['seg-sex' => range(0, 4), 'ter-sab' => range(1, 5), 'sab' => [5], 'dom' => [6]];
        $hoursByDay = [];

        foreach ($compactHours as $range => $hours) {
            if (! isset($ranges[$range]) || ! str_contains($hours, '-')) {
                continue;
            }

            [$open, $close] = explode('-', $hours, 2);
            foreach ($ranges[$range] as $day) {
                $hoursByDay[$day] = [$open, $close];
            }
        }

        if ($hoursByDay === []) {
            return null;
        }

        return array_map(fn (string $day, int $index) => [
            'day' => $day,
            'open' => $hoursByDay[$index][0] ?? '',
            'close' => $hoursByDay[$index][1] ?? '',
            'closed' => ! isset($hoursByDay[$index]),
        ], $days, array_keys($days));
    }
};

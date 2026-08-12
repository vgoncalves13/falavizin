<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = [
            'founder_program_enabled' => '1',
            'founder_program_starts_at' => null,
            'founder_program_ends_at' => null,
            'founder_max_participants' => '0',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('key', [
                'founder_program_enabled',
                'founder_program_starts_at',
                'founder_program_ends_at',
                'founder_max_participants',
            ])
            ->delete();
    }
};

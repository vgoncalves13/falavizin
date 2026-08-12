<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $businesses = DB::table('businesses')
            ->whereNotNull('category_id')
            ->whereNotIn('id', DB::table('business_categories')->select('business_id'))
            ->get(['id', 'category_id', 'created_at', 'updated_at']);

        $rows = $businesses->map(fn ($business) => [
            'business_id' => $business->id,
            'category_id' => $business->category_id,
            'created_at' => $business->created_at,
            'updated_at' => $business->updated_at,
        ])->all();

        if (! empty($rows)) {
            DB::table('business_categories')->insertOrIgnore($rows);
        }
    }

    public function down(): void
    {
        $businesses = DB::table('businesses')
            ->whereNotNull('category_id')
            ->get(['id', 'category_id']);

        foreach ($businesses as $business) {
            DB::table('business_categories')
                ->where('business_id', $business->id)
                ->where('category_id', $business->category_id)
                ->delete();
        }
    }
};

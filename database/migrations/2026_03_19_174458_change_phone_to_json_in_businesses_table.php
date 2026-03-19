<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing varchar values to JSON arrays before changing the column type
        DB::statement("UPDATE businesses SET phone = JSON_ARRAY(phone) WHERE phone IS NOT NULL AND phone != ''");

        DB::statement('ALTER TABLE businesses MODIFY phone JSON NULL');
    }

    public function down(): void
    {
        // Extract first element from JSON array back to varchar
        DB::statement("UPDATE businesses SET phone = JSON_UNQUOTE(JSON_EXTRACT(phone, '$[0]')) WHERE phone IS NOT NULL");

        Schema::table('businesses', function ($table) {
            $table->string('phone')->nullable()->change();
        });
    }
};

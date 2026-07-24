<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('neighborhoods', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('city');
            $table->string('city_slug');
            $table->string('state_code', 2);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['state_code', 'city_slug', 'slug']);
            $table->index(['is_active', 'sort_order']);
        });

        $latitude = DB::table('settings')->where('key', 'neighborhood_lat')->value('value');
        $longitude = DB::table('settings')->where('key', 'neighborhood_lng')->value('value');

        DB::table('neighborhoods')->insertOrIgnore([
            'name' => 'Engenho da Rainha',
            'slug' => 'engenho-da-rainha',
            'city' => 'Rio de Janeiro',
            'city_slug' => 'rio-de-janeiro',
            'state_code' => 'RJ',
            'latitude' => is_numeric($latitude) ? $latitude : null,
            'longitude' => is_numeric($longitude) ? $longitude : null,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
    }
};

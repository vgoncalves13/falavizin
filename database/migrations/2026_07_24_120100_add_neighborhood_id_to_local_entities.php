<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('neighborhood_id')
                ->nullable()
                ->after('neighborhood')
                ->constrained()
                ->restrictOnDelete();
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('neighborhood_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->index(
                ['neighborhood_id', 'status', 'created_at'],
                'posts_neighborhood_status_created_index',
            );
            $table->index(
                ['neighborhood_id', 'status', 'event_starts_at'],
                'posts_neighborhood_status_event_index',
            );
        });

        Schema::table('businesses', function (Blueprint $table): void {
            $table->foreignId('neighborhood_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->index(
                ['neighborhood_id', 'status', 'category_id'],
                'businesses_neighborhood_status_category_index',
            );
        });

        $pilotId = DB::table('neighborhoods')
            ->where('state_code', 'RJ')
            ->where('city_slug', 'rio-de-janeiro')
            ->where('slug', 'engenho-da-rainha')
            ->value('id');

        throw_unless($pilotId, RuntimeException::class, 'Pilot neighborhood was not created.');

        foreach (['users', 'posts', 'businesses'] as $table) {
            DB::table($table)->whereNull('neighborhood_id')->update(['neighborhood_id' => $pilotId]);
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropForeign(['neighborhood_id']);
            $table->dropIndex('businesses_neighborhood_status_category_index');
            $table->dropColumn('neighborhood_id');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropForeign(['neighborhood_id']);
            $table->dropIndex('posts_neighborhood_status_created_index');
            $table->dropIndex('posts_neighborhood_status_event_index');
            $table->dropColumn('neighborhood_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['neighborhood_id']);
            $table->dropColumn('neighborhood_id');
        });
    }
};

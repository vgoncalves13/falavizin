<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NeighborhoodMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_expansion_migration_backfills_all_local_entities(): void
    {
        $relations = require database_path('migrations/2026_07_24_120100_add_neighborhood_id_to_local_entities.php');
        $neighborhoods = require database_path('migrations/2026_07_24_120000_create_neighborhoods_table.php');

        $relations->down();
        $neighborhoods->down();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Morador legado',
            'email' => 'legado@example.com',
            'password' => bcrypt('password'),
            'neighborhood' => 'Engenho da Rainha',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Aviso',
            'slug' => 'aviso-legado',
            'type' => 'post',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('posts')->insert([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => 'Post legado',
            'slug' => 'post-legado',
            'body' => 'Conteúdo legado para o teste.',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('businesses')->insert([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'name' => 'Negócio legado',
            'slug' => 'negocio-legado',
            'neighborhood' => 'Engenho da Rainha',
            'city' => 'Rio de Janeiro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $neighborhoods->up();
        $relations->up();

        $pilotId = DB::table('neighborhoods')
            ->where('slug', 'engenho-da-rainha')
            ->value('id');

        $this->assertNotNull($pilotId);
        $this->assertSame(0, DB::table('users')->whereNull('neighborhood_id')->count());
        $this->assertSame(0, DB::table('posts')->whereNull('neighborhood_id')->count());
        $this->assertSame(0, DB::table('businesses')->whereNull('neighborhood_id')->count());
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('categories')->where('slug', 'pedido')->doesntExist()) {
            DB::table('categories')->insert([
                'name' => 'Pedido',
                'slug' => 'pedido',
                'icon' => 'hand-raised',
                'type' => 'post',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('categories')->where('slug', 'pedido')->delete();
    }
};

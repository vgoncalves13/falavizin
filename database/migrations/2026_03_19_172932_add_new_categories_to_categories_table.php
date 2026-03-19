<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['name' => 'Academia',          'slug' => 'academia',       'icon' => 'trophy',             'type' => 'business', 'sort_order' => 15],
            ['name' => 'Automotivo',         'slug' => 'automotivo',     'icon' => 'cog-6-tooth',        'type' => 'business', 'sort_order' => 16],
            ['name' => 'Banco & Finanças',   'slug' => 'banco',          'icon' => 'banknotes',          'type' => 'business', 'sort_order' => 17],
            ['name' => 'Casa & Construção',  'slug' => 'casa',           'icon' => 'building-office-2',  'type' => 'business', 'sort_order' => 18],
            ['name' => 'Moda',               'slug' => 'moda',           'icon' => 'shopping-bag',       'type' => 'business', 'sort_order' => 19],
            ['name' => 'Serviços',           'slug' => 'servicos',       'icon' => 'briefcase',          'type' => 'business', 'sort_order' => 20],
            ['name' => 'Transporte',         'slug' => 'transporte',     'icon' => 'truck',              'type' => 'business', 'sort_order' => 21],
            ['name' => 'Entretenimento',     'slug' => 'entretenimento', 'icon' => 'musical-note',       'type' => 'business', 'sort_order' => 22],
            ['name' => 'Outros',             'slug' => 'outros',         'icon' => 'ellipsis-horizontal', 'type' => 'business', 'sort_order' => 99],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insertOrIgnore(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('categories')->whereIn('slug', [
            'academia', 'automotivo', 'banco', 'casa',
            'moda', 'servicos', 'transporte', 'entretenimento',
            'outros',
        ])->delete();
    }
};

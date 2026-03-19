<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@hudobairro.com.br',
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->seedCategories();
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Aviso', 'slug' => 'aviso', 'icon' => 'megaphone', 'type' => 'post', 'sort_order' => 1],
            ['name' => 'Problema', 'slug' => 'problema', 'icon' => 'exclamation-triangle', 'type' => 'post', 'sort_order' => 2],
            ['name' => 'Evento', 'slug' => 'evento', 'icon' => 'calendar-days', 'type' => 'post', 'sort_order' => 3],
            ['name' => 'Achado e Perdido', 'slug' => 'achado-perdido', 'icon' => 'magnifying-glass', 'type' => 'post', 'sort_order' => 4],
            ['name' => 'Alimentação', 'slug' => 'alimentacao', 'icon' => 'cake', 'type' => 'both', 'sort_order' => 5],
            ['name' => 'Mercado', 'slug' => 'mercado', 'icon' => 'shopping-cart', 'type' => 'business', 'sort_order' => 6],
            ['name' => 'Saúde', 'slug' => 'saude', 'icon' => 'heart', 'type' => 'business', 'sort_order' => 7],
            ['name' => 'Pet', 'slug' => 'pet', 'icon' => 'face-smile', 'type' => 'business', 'sort_order' => 8],
            ['name' => 'Elétrica', 'slug' => 'eletrica', 'icon' => 'bolt', 'type' => 'business', 'sort_order' => 9],
            ['name' => 'Encanamento', 'slug' => 'encanamento', 'icon' => 'wrench', 'type' => 'business', 'sort_order' => 10],
            ['name' => 'Pintura', 'slug' => 'pintura', 'icon' => 'paint-brush', 'type' => 'business', 'sort_order' => 11],
            ['name' => 'Internet', 'slug' => 'internet', 'icon' => 'wifi', 'type' => 'business', 'sort_order' => 12],
            ['name' => 'Educação', 'slug' => 'educacao', 'icon' => 'academic-cap', 'type' => 'business', 'sort_order' => 13],
            ['name' => 'Beleza',          'slug' => 'beleza',         'icon' => 'sparkles',           'type' => 'business', 'sort_order' => 14],
            ['name' => 'Academia',        'slug' => 'academia',       'icon' => 'trophy',             'type' => 'business', 'sort_order' => 15],
            ['name' => 'Automotivo',      'slug' => 'automotivo',     'icon' => 'cog-6-tooth',        'type' => 'business', 'sort_order' => 16],
            ['name' => 'Banco & Finanças', 'slug' => 'banco',          'icon' => 'banknotes',          'type' => 'business', 'sort_order' => 17],
            ['name' => 'Casa & Construção', 'slug' => 'casa',          'icon' => 'building-office-2',  'type' => 'business', 'sort_order' => 18],
            ['name' => 'Moda',            'slug' => 'moda',           'icon' => 'shopping-bag',       'type' => 'business', 'sort_order' => 19],
            ['name' => 'Serviços',        'slug' => 'servicos',       'icon' => 'briefcase',          'type' => 'business', 'sort_order' => 20],
            ['name' => 'Transporte',      'slug' => 'transporte',     'icon' => 'truck',              'type' => 'business', 'sort_order' => 21],
            ['name' => 'Entretenimento',  'slug' => 'entretenimento', 'icon' => 'musical-note',        'type' => 'business', 'sort_order' => 22],
            ['name' => 'Outros',          'slug' => 'outros',         'icon' => 'ellipsis-horizontal',  'type' => 'business', 'sort_order' => 99],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}

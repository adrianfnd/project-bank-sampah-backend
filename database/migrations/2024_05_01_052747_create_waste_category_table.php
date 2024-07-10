<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WasteCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Botol', 'price_per_unit' => 1500.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Buku/Arsip', 'price_per_unit' => 500.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Dus', 'price_per_unit' => 800.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Galon', 'price_per_unit' => 5000.00, 'unit' => 'piece', 'type' => 'recyclable'],
            ['name' => 'Duplek', 'price_per_unit' => 750.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Emberan', 'price_per_unit' => 1000.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Plastik Putih', 'price_per_unit' => 500.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Plastik Hitam', 'price_per_unit' => 200.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Besi', 'price_per_unit' => 2500.00, 'unit' => 'kg', 'type' => 'recyclable'],
            ['name' => 'Kaleng', 'price_per_unit' => 1000.00, 'unit' => 'piece', 'type' => 'recyclable'],
            ['name' => 'Kertas', 'price_per_unit' => 300.00, 'unit' => 'kg', 'type' => 'recyclable'], // Kategori tambahan
        ];

        foreach ($categories as $category) {
            DB::table('waste_categories')->insert($category);
        }
    }
}

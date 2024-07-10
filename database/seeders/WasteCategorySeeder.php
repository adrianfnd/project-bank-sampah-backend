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
            ['name' => 'Botol', 'price_per_unit' => 1500.00, 'unit' => 'kg'],
            ['name' => 'Buku/Arsip', 'price_per_unit' => 500.00, 'unit' => 'kg'],
            ['name' => 'Dus', 'price_per_unit' => 800.00, 'unit' => 'kg'],
            ['name' => 'Galon', 'price_per_unit' => 5000.00, 'unit' => 'piece'],
            ['name' => 'Duplek', 'price_per_unit' => 750.00, 'unit' => 'kg'],
            ['name' => 'Emberan', 'price_per_unit' => 1000.00, 'unit' => 'kg'],
            ['name' => 'Plastik Putih', 'price_per_unit' => 500.00, 'unit' => 'kg'],
            ['name' => 'Plastik Hitam', 'price_per_unit' => 200.00, 'unit' => 'kg'],
            ['name' => 'Besi', 'price_per_unit' => 2500.00, 'unit' => 'kg'],
            ['name' => 'Kaleng', 'price_per_unit' => 1000.00, 'unit' => 'piece'],
        ];

        foreach ($categories as $category) {
            DB::table('waste_categories')->insert($category);
        }
    }
}

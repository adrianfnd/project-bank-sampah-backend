<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [];
        for ($i = 1; $i <= 10; $i++) {
            $products[] = [
                'id' => $i,
                'name' => 'Product ' . $i,
                'description' => 'Description for product ' . $i,
                'image' => null,
                'point_cost' => rand(10, 100),
                'stock' => rand(1, 50),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('products')->insert($products);
    }
}
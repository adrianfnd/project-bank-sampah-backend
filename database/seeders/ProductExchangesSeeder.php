<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductExchangesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exchanges = [];
        for ($i = 1; $i <= 10; $i++) {
            $exchanges[] = [
                'id' => $i,
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'product_id' => DB::table('products')->inRandomOrder()->first()->id,
                'quantity' => rand(1, 10),
                'total_points' => rand(10, 100),
                'exchange_date' => now(),
                'created_by' => DB::table('users')->inRandomOrder()->first()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('product_exchanges')->insert($exchanges);
    }
}
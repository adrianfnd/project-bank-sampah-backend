<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WastesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wastes = [];
        for ($i = 1; $i <= 10; $i++) {
            $amount = rand(10000, 100000);
            $wastes[] = [
                'id' => $i,
                'name' => 'Waste ' . $i,
                'category_id' => DB::table('waste_categories')->inRandomOrder()->first()->id,
                'weight' => rand(1, 10),
                'point' => $amount,
                'waste_collection_id' => DB::table('waste_collections')->inRandomOrder()->first()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('wastes')->insert($wastes);
    }
}
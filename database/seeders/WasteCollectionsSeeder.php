<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WasteCollectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collections = [];
        for ($i = 1; $i <= 10; $i++) {
            $collections[] = [
                'id' => Str::uuid(),
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'waste_id' => DB::table('wastes')->inRandomOrder()->first()->id,
                'weight_total' => rand(1, 10),
                'point_total' => rand(1, 100),
                'collection_date' => now(),
                'created_by' => DB::table('users')->inRandomOrder()->first()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('waste_collections')->insert($collections);
    }
}
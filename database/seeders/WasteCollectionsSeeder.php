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
            $amount = rand(10000, 100000);
            $collections[] = [
                'id' => Str::uuid(),
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'weight_total' => rand(1, 10),
                'point_total' => $amount,
                'address' => Str::random(10),
                'collection_date' => now(),
                'confirmation_status' => 'berhasil',
                'created_by' => DB::table('users')->inRandomOrder()->first()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('waste_collections')->insert($collections);
    }
}
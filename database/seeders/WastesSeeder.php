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
            $wastes[] = [
                'id' => $i,
                'name' => 'Waste ' . $i,
                'category' => ['organic', 'non_organic', 'b3'][array_rand(['organic', 'non_organic', 'b3'])],
                'weight' => rand(1, 10),
                'point' => rand(1, 100),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('wastes')->insert($wastes);
    }
}
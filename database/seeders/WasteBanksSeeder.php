<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WasteBanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [];
        for ($i = 1; $i <= 10; $i++) {
            $banks[] = [
                'id' => Str::uuid(),
                'name' => 'Waste Bank ' . $i,
                'address' => 'Address ' . $i,
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('waste_banks')->insert($banks);
    }
}
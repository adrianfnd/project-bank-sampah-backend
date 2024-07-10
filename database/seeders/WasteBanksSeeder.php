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
        $customerRoleId = DB::table('roles')->where('name', 'costumer')->value('id');
        $customerUsers = DB::table('users')->where('role_id', $customerRoleId)->get();

        foreach ($customerUsers as $user) {
            $wasteBank = [
                'id' => Str::uuid(),
                'name' => 'Desa Sindangpanon',
                'address' => 'Sindangpanon, Banjaran, Kabupaten Bandung, Jawa Barat',
                'user_id' => $user->id,
                'longitude' => '107.58677990',
                'latitude' => '-7.07736310',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('waste_banks')->insert($wasteBank);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class UserRoleHasPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permission Admin
        for ($i = 1; $i <= 23; $i++) {
            DB::table('role_has_permission')->insert([
                'role_id' => 1,
                'permission_id' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Permission Staff
        $permission_staffs = [8, 10, 12, 13, 14];

        foreach ($permission_staffs as $permission_staff) {
            DB::table('role_has_permission')->insert([
                'role_id' => 2,
                'permission_id' => $permission_staff,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        
        // Permission Customer
        $permission_costumers = [1, 6, 8, 11, 16, 17];

        foreach ($permission_costumers as $permission_costumer) {
            DB::table('role_has_permission')->insert([
                'role_id' => 3,
                'permission_id' => $permission_costumer,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

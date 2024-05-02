<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class UserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_permissions')->insert([
            // Product
            [
                'permission_name' => 'get_products',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'create_products',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'update_products',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'delete_products',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // User 
            [
                'permission_name' => 'get_users',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'get_user_by_id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'create_users',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'update_users',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'delete_users',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Waster Collection
            [
                'permission_name' => 'get_waste_collections',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'get_waste_collection_by_user_id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'create_waste_collections',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'update_waste_collections',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'delete_waste_collections',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Transaction
            [
                'permission_name' => 'get_transactions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'get_transaction_by_user_id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'create_transactions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'update_transactions',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Waste Bank
            [
                'permission_name' => 'get_waste_banks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'get_waste_bank_by_user_id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'create_waste_banks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'update_waste_banks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_name' => 'delete_waste_banks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');

        $users = [
            [
                'id' => Str::uuid(),
                'name' => 'Admin User',
                'address' => 'Jl Bandung',
                'email' => 'admin@gmail.com',
                'phone_number' => '0812345678901',
                'image' => null,
                'otp' => null,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'current_point' => 0,
                'role_id' => $roles['admin'],
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Staff User',
                'address' => 'Jl Bandung',
                'email' => 'staff@gmail.com',
                'phone_number' => '0812345678902',
                'image' => null,
                'otp' => null,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'current_point' => 0,
                'role_id' => $roles['staff'],
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Customer User',
                'address' => 'Jl Bandung',
                'email' => 'customer@gmail.com',
                'phone_number' => '0812345678903',
                'image' => null,
                'otp' => null,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'current_point' => 0,
                'role_id' => $roles['costumer'],
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('users')->insert($users);
    }
}
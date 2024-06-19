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
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'id' => Str::uuid(),
                'name' => 'User ' . $i,
                'address' => 'Address ' . $i,
                'email' => 'user' . $i . '@example.com',
                'phone_number' => '08123456789' . $i,
                'image' => null,
                'otp' => null,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'current_point' => 0,
                'role_id' => rand(1, 3),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('users')->insert($users);
    }
}
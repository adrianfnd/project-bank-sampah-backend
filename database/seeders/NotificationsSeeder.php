<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [];
        for ($i = 1; $i <= 10; $i++) {
            $notifications[] = [
                'id' => $i,
                'title' => 'Notification ' . $i,
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'description' => 'Description for notification ' . $i,
                'type' => 'info',
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('notifications')->insert($notifications);
    }
}
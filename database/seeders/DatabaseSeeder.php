<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            UserRolePermissionSeeder::class,
            UserRoleHasPermissionSeeder::class,
        ]);

        // Development
        $this->call([
            UsersSeeder::class,
            XenditLogsSeeder::class,
            WastesSeeder::class,
            WasteBanksSeeder::class,
            WasteCollectionsSeeder::class,
            TransactionsSeeder::class,
            ProductsSeeder::class,
            ProductExchangesSeeder::class,
            PpobPaymentsSeeder::class,
            NotificationsSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class XenditLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logs = [];
        for ($i = 1; $i <= 10; $i++) {
            $amount = rand(10000, 100000);
            $logs[] = [
                'id' => Str::random(10),
                'external_id' => Str::random(10),
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'is_high' => 'false',
                'payment_method' => 'bank_transfer',
                'status' => 'success',
                'merchant_name' => 'Merchant ' . $i,
                'amount' => $amount,
                'paid_amount' => $amount,
                'bank_code' => 'BCA',
                'paid_at' => now(),
                'payer_email' => 'payer' . $i . '@example.com',
                'description' => 'Payment for order ' . $i,
                'adjusted_received_amount' => $amount,
                'fees_paid_amount' => rand(1000, 10000),
                'updated' => now(),
                'created' => now(),
                'currency' => 'IDR',
                'payment_channel' => 'BCA',
                'payment_destination' => 'BCA account'
            ];
        }

        DB::table('xendit_logs')->insert($logs);
    }
}
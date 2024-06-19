<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PpobPaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = [];
        for ($i = 1; $i <= 10; $i++) {
            $amount = rand(10000, 100000);
            $payments[] = [
                'id' => $i,
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'transaction_id' => DB::table('transactions')->inRandomOrder()->first()->id,
                'biller_name' => 'Biller ' . $i,
                'biller_account' => 'Account ' . $i,
                'amount' => $amount,
                'payment_date' => now(),
                'created_by' => DB::table('users')->inRandomOrder()->first()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('ppob_payments')->insert($payments);
    }
}
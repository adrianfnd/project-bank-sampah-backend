<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [];
        for ($i = 1; $i <= 10; $i++) {
            $transactions[] = [
                'id' => $i,
                'transaction_type' => ['penarikan', 'pertukaran_produk', 'pembayaran_tagihan'][array_rand(['penarikan', 'pertukaran_produk', 'pembayaran_tagihan'])],
                'total_balance_involved' => rand(10000, 100000),
                'user_id' => DB::table('users')->inRandomOrder()->first()->id,
                'description' => 'Transaction ' . $i,
                'created_by' => DB::table('users')->inRandomOrder()->first()->id,
                'xendit_id' => DB::table('xendit_logs')->inRandomOrder()->first()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('transactions')->insert($transactions);
    }
}
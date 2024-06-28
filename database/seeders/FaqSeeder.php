<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            ['question' => 'Bagaimana cara mendapatkan point?', 'answer' => 'Anda bisa mendapatkan point dengan mengumpulkan dan menyerahkan sampah ke bank sampah terdekat.', 'created_at' => now(), 'updated_at' => now()],
            ['question' => 'Bagaimana cara menukarkan point?', 'answer' => 'Anda bisa menukarkan point dengan berbagai hadiah atau voucher melalui aplikasi atau website kami.', 'created_at' => now(), 'updated_at' => now()],
            ['question' => 'Bagaimana cara melihat informasi point?', 'answer' => 'Informasi point bisa dilihat di dashboard akun Anda pada aplikasi atau website kami.', 'created_at' => now(), 'updated_at' => now()],
            ['question' => 'Bagaimana cara melihat pencatatan penukaran sampah?', 'answer' => 'Anda bisa melihat pencatatan penukaran sampah di riwayat transaksi pada akun Anda.', 'created_at' => now(), 'updated_at' => now()],
            ['question' => 'Bagaimana cara melihat pencatatan penukaran point?', 'answer' => 'Anda bisa melihat pencatatan penukaran point di riwayat penukaran pada akun Anda.', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('faqs')->insert($faqs);
    }
}

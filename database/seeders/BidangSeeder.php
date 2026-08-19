<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangSeeder extends Seeder
{
    public function run(): void
    {
        $bidangs = [
            ['id' => 1, 'code' => 'SEKRETARIAT', 'name' => 'Sekretariat', 'description' => 'Sekretariat Diskominfo Jabar'],
            ['id' => 2, 'code' => 'EGOV',        'name' => 'Bidang E-Government', 'description' => 'Bidang E-Government'],
            ['id' => 3, 'code' => 'APTIKA',      'name' => 'Bidang Aplikasi Informatika', 'description' => 'Bidang Aplikasi Informatika'],
            ['id' => 4, 'code' => 'IKP',         'name' => 'Bidang Informasi dan Komunikasi Publik', 'description' => 'Bidang Informasi dan Komunikasi Publik'],
            ['id' => 5, 'code' => 'PERSANDIAN',  'name' => 'Bidang Persandian dan Keamanan Informasi', 'description' => 'Bidang Persandian dan Keamanan Informasi'],
            ['id' => 6, 'code' => 'STATISTIK',   'name' => 'Bidang Statistik', 'description' => 'Bidang Statistik'],
            ['id' => 7, 'code' => 'PLDDIG',      'name' => 'UPTD Pusat Layanan Digital', 'description' => 'UPTD PLDDIG Jawa Barat'],
        ];

        foreach ($bidangs as $b) {
            DB::table('bidangs')->updateOrInsert(
                ['id' => $b['id']],
                [
                    'code' => $b['code'],
                    'name' => $b['name'],
                    'description' => $b['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}

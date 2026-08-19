<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['id' => 1, 'code' => 'ADMINISTRASI_SURAT', 'name' => 'Administrasi Surat', 'description' => 'Nota Dinas, SPD, Hasil Pentest, Kerentanan, Permohonan TI'],
            ['id' => 2, 'code' => 'IKI_REPORT',         'name' => 'IKI Report',         'description' => 'Integrasi Interoperabilitas, Pengelolaan Aplikasi, Rekayasa Aplikasi, Sidebar Jabar, Smart Jabar, Sada Jabar'],
            ['id' => 3, 'code' => 'MANAJEMEN_TUGAS',    'name' => 'Manajemen Tugas Digital', 'description' => 'Scrum/Kanban Board, Task Management'],
            ['id' => 4, 'code' => 'MAGANG',             'name' => 'Magang',             'description' => 'Pendaftaran, Presensi, NDA, Sertifikat Magang'],
        ];

        foreach ($services as $s) {
            DB::table('services')->updateOrInsert(
                ['id' => $s['id']],
                [
                    'code' => $s['code'],
                    'name' => $s['name'],
                    'description' => $s['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}

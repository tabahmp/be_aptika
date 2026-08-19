<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Layanan Utama (Main Services)
            ['id' => 1, 'parent_id' => null, 'code' => 'ADMINISTRASI_SURAT', 'name' => 'Administrasi Surat', 'description' => 'Induk Layanan Administrasi Surat'],
            ['id' => 2, 'parent_id' => null, 'code' => 'IKI_REPORT',         'name' => 'IKI Report',         'description' => 'Induk Laporan Kinerja Indikator Aplikasi'],
            ['id' => 3, 'parent_id' => null, 'code' => 'MANAJEMEN_TUGAS',    'name' => 'Manajemen Tugas Digital', 'description' => 'Scrum/Kanban Board & Task Management'],
            ['id' => 4, 'parent_id' => null, 'code' => 'MAGANG',             'name' => 'Magang',             'description' => 'Pendaftaran, Presensi, NDA, & Sertifikat Magang'],

            // Sub-Layanan Jenis Surat (under ADMINISTRASI_SURAT)
            ['id' => 5, 'parent_id' => 1, 'code' => 'SURAT_NOTA_DINAS',    'name' => 'Nota Dinas',              'description' => 'Modul Pengelolaan Nota Dinas'],
            ['id' => 6, 'parent_id' => 1, 'code' => 'SURAT_SPD',           'name' => 'Surat Perjalanan Dinas (SPD)', 'description' => 'Modul Perjalanan Dinas & Rekening'],
            ['id' => 7, 'parent_id' => 1, 'code' => 'SURAT_HASIL_PENTEST', 'name' => 'Laporan Hasil Pentest',   'description' => 'Modul Hasil Pentest Aplikasi'],
            ['id' => 8, 'parent_id' => 1, 'code' => 'SURAT_KERENTANAN',    'name' => 'Laporan Kerentanan',      'description' => 'Modul Kerentanan Keamanan'],
            ['id' => 9, 'parent_id' => 1, 'code' => 'SURAT_PERMOHONAN_TI', 'name' => 'Form Perubahan IT (RFC)', 'description' => 'Modul Permohonan Perubahan IT'],

            // Sub-Layanan Laporan IKI (under IKI_REPORT)
            ['id' => 10, 'parent_id' => 2, 'code' => 'IKI_INTEGRASI',  'name' => 'Integrasi Interoperabilitas', 'description' => 'Sub-modul Integrasi Interoperabilitas'],
            ['id' => 11, 'parent_id' => 2, 'code' => 'IKI_PENGELOLAAN', 'name' => 'Pengelolaan Aplikasi',        'description' => 'Sub-modul Pengelolaan Aplikasi'],
            ['id' => 12, 'parent_id' => 2, 'code' => 'IKI_REKAYASA',    'name' => 'Rekayasa Aplikasi',           'description' => 'Sub-modul Rekayasa Aplikasi'],
            ['id' => 13, 'parent_id' => 2, 'code' => 'IKI_SIDEBAR',     'name' => 'Sidebar Jabar',               'description' => 'Sub-modul Sidebar Jabar'],
            ['id' => 14, 'parent_id' => 2, 'code' => 'IKI_SMARTJABAR',  'name' => 'Smart Jabar',                 'description' => 'Sub-modul Smart Jabar'],
            ['id' => 15, 'parent_id' => 2, 'code' => 'IKI_SADAJABAR',   'name' => 'Sada Jabar',                  'description' => 'Sub-modul Sada Jabar'],
        ];

        foreach ($services as $s) {
            DB::table('services')->updateOrInsert(
                ['id' => $s['id']],
                [
                    'parent_id' => $s['parent_id'],
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

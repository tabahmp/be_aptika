<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'parent_id')) {
                $table->foreignId('parent_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('services')
                      ->onDelete('cascade');
            }
        });

        // Seed/Update master bidangs (1 s/d 7)
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

        // Seed/Update master services
        $services = [
            ['id' => 1, 'parent_id' => null, 'code' => 'ADMINISTRASI_SURAT', 'name' => 'Administrasi Surat', 'description' => 'Induk Layanan Administrasi Surat'],
            ['id' => 2, 'parent_id' => null, 'code' => 'IKI_REPORT',         'name' => 'IKI Report',         'description' => 'Induk Laporan Kinerja Indikator Aplikasi'],
            ['id' => 3, 'parent_id' => null, 'code' => 'MANAJEMEN_TUGAS',    'name' => 'Manajemen Tugas Digital', 'description' => 'Scrum/Kanban Board & Task Management'],
            ['id' => 4, 'parent_id' => null, 'code' => 'MAGANG',             'name' => 'Magang',             'description' => 'Pendaftaran, Presensi, NDA, & Sertifikat Magang'],

            ['id' => 5, 'parent_id' => 1, 'code' => 'SURAT_NOTA_DINAS',    'name' => 'Nota Dinas',              'description' => 'Modul Pengelolaan Nota Dinas'],
            ['id' => 6, 'parent_id' => 1, 'code' => 'SURAT_SPD',           'name' => 'Surat Perjalanan Dinas (SPD)', 'description' => 'Modul Perjalanan Dinas & Rekening'],
            ['id' => 7, 'parent_id' => 1, 'code' => 'SURAT_HASIL_PENTEST', 'name' => 'Laporan Hasil Pentest',   'description' => 'Modul Hasil Pentest Aplikasi'],
            ['id' => 8, 'parent_id' => 1, 'code' => 'SURAT_KERENTANAN',    'name' => 'Laporan Kerentanan',      'description' => 'Modul Kerentanan Keamanan'],
            ['id' => 9, 'parent_id' => 1, 'code' => 'SURAT_PERMOHONAN_TI', 'name' => 'Form Perubahan IT (RFC)', 'description' => 'Modul Permohonan Perubahan IT'],

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

        // Seed/Update bidang_services mapping
        if (Schema::hasTable('bidang_services')) {
            $bidangIds = [1, 2, 3, 4, 5, 6, 7];
            $serviceIds = range(1, 15);
            $ikiServiceIds = [2, 10, 11, 12, 13, 14, 15];

            foreach ($bidangIds as $bId) {
                foreach ($serviceIds as $sId) {
                    $isEnabled = in_array($sId, $ikiServiceIds) ? ($bId === 3) : true;
                    DB::table('bidang_services')->updateOrInsert(
                        ['bidang_id' => $bId, 'service_id' => $sId],
                        [
                            'is_enabled' => $isEnabled,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};

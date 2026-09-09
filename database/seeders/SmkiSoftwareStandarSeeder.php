<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmkiSoftwareStandarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Kategori
        $kategoris = [
            ['nama_kategori' => 'Lisensi', 'keterangan' => 'Software berbayar/lisensi komersial resmi'],
            ['nama_kategori' => 'Open source', 'keterangan' => 'Software sumber terbuka dengan lisensi publik'],
            ['nama_kategori' => 'In house', 'keterangan' => 'Software/aplikasi mandiri hasil pengembangan internal organisasi'],
        ];

        foreach ($kategoris as $k) {
            DB::table('smki_kategoris')->updateOrInsert(
                ['nama_kategori' => $k['nama_kategori']],
                ['keterangan' => $k['keterangan'], 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // 2. Seed Tipe Software
        $tipes = [
            ['nama_tipe_software' => 'Operating System', 'keterangan' => 'Sistem operasi perangkat keras'],
            ['nama_tipe_software' => 'Aplikasi perkantoran', 'keterangan' => 'Office productivity suite'],
            ['nama_tipe_software' => 'Web Application', 'keterangan' => 'Aplikasi berbasis peramban web'],
            ['nama_tipe_software' => 'Desktop Application', 'keterangan' => 'Aplikasi mandiri yang terpasang di desktop'],
            ['nama_tipe_software' => 'Browser', 'keterangan' => 'Peramban web internet'],
            ['nama_tipe_software' => 'Development Tool', 'keterangan' => 'Alat pemrograman, IDE, dan compiler'],
            ['nama_tipe_software' => 'Communication', 'keterangan' => 'Perangkat kolaborasi dan rapat daring'],
            ['nama_tipe_software' => 'Security & Antivirus', 'keterangan' => 'Perangkat pengamanan dan proteksi endpoint'],
            ['nama_tipe_software' => 'System Software', 'keterangan' => 'Perangkat lunak utilitas sistem'],
            ['nama_tipe_software' => 'Database Management', 'keterangan' => 'Sistem manajemen basis data'],
        ];

        foreach ($tipes as $t) {
            DB::table('smki_tipe_softwares')->updateOrInsert(
                ['nama_tipe_software' => $t['nama_tipe_software']],
                ['keterangan' => $t['keterangan'], 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // 3. Seed Penyedia Barang / Vendor
        $vendors = [
            ['nama_penyedia_barang' => 'Microsoft Corporation', 'kontak_vendor' => 'support@microsoft.com'],
            ['nama_penyedia_barang' => 'Apple Inc.', 'kontak_vendor' => 'enterprise@apple.com'],
            ['nama_penyedia_barang' => 'Adobe Systems', 'kontak_vendor' => 'license@adobe.com'],
            ['nama_penyedia_barang' => 'Google LLC', 'kontak_vendor' => 'support@google.com'],
            ['nama_penyedia_barang' => 'Oracle Corporation', 'kontak_vendor' => 'support@oracle.com'],
            ['nama_penyedia_barang' => 'Autodesk Inc.', 'kontak_vendor' => 'support@autodesk.com'],
            ['nama_penyedia_barang' => 'Zoom Video Communications', 'kontak_vendor' => 'info@zoom.us'],
            ['nama_penyedia_barang' => 'Slack Technologies', 'kontak_vendor' => 'feedback@slack.com'],
            ['nama_penyedia_barang' => 'Postman Inc.', 'kontak_vendor' => 'help@postman.com'],
            ['nama_penyedia_barang' => 'Mozilla Foundation', 'kontak_vendor' => 'privacy@mozilla.org'],
            ['nama_penyedia_barang' => 'Diskominfo Jawa Barat', 'kontak_vendor' => 'diskominfo@jabarprov.go.id'],
        ];

        foreach ($vendors as $v) {
            DB::table('smki_penyedia_barangs')->updateOrInsert(
                ['nama_penyedia_barang' => $v['nama_penyedia_barang']],
                ['kontak_vendor' => $v['kontak_vendor'], 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // 4. Sub-service SMKI_SOFTWARE_STANDAR pada tabel services
        $smkiParent = DB::table('services')->where('code', 'SMKI')->first();
        $parentId = $smkiParent ? $smkiParent->id : 16;

        DB::table('services')->updateOrInsert(
            ['code' => 'SMKI_SOFTWARE_STANDAR'],
            [
                'id'          => 17,
                'parent_id'   => $parentId,
                'code'        => 'SMKI_SOFTWARE_STANDAR',
                'name'        => 'Daftar Software Standar',
                'description' => 'Inventarisasi dan Pengelolaan Daftar Software Standar SMKI (FR-017)',
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );

        // Aktifkan pada seluruh bidang_services
        $bidangIds = DB::table('bidangs')->pluck('id')->toArray();
        if (empty($bidangIds)) {
            $bidangIds = [1, 2, 3, 4, 5, 6, 7];
        }

        foreach ($bidangIds as $bId) {
            DB::table('bidang_services')->updateOrInsert(
                ['bidang_id' => $bId, 'service_id' => 17],
                ['is_enabled' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // 5. Seed Initial Software Standar Data (Official FR-017 Baseline)
        $katMap = DB::table('smki_kategoris')->pluck('id', 'nama_kategori');
        $tipeMap = DB::table('smki_tipe_softwares')->pluck('id', 'nama_tipe_software');
        $vendorMap = DB::table('smki_penyedia_barangs')->pluck('id', 'nama_penyedia_barang');

        $initialSoftware = [
            [
                'nama_software'       => 'Microsoft Windows',
                'tipe_software_id'    => $tipeMap['Operating System'] ?? null,
                'versi'               => '10 Pro (64-bit)',
                'penyedia_barang_id'  => $vendorMap['Microsoft Corporation'] ?? null,
                'kategori_id'         => $katMap['Lisensi'] ?? null,
                'keterangan'          => 'Sistem operasi standar workstation kantor',
                'bidang_id'           => 5, // Bidang Persandian & Keamanan Informasi (atau default)
            ],
            [
                'nama_software'       => 'Microsoft Windows',
                'tipe_software_id'    => $tipeMap['Operating System'] ?? null,
                'versi'               => '11 Pro (Build 22631)',
                'penyedia_barang_id'  => $vendorMap['Microsoft Corporation'] ?? null,
                'kategori_id'         => $katMap['Lisensi'] ?? null,
                'keterangan'          => 'Sistem operasi standar perangkat generasi baru',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Mac OS',
                'tipe_software_id'    => $tipeMap['Operating System'] ?? null,
                'versi'               => 'macOS 12 (Monterey) / 13 (Ventura)',
                'penyedia_barang_id'  => $vendorMap['Apple Inc.'] ?? null,
                'kategori_id'         => $katMap['Lisensi'] ?? null,
                'keterangan'          => 'Sistem operasi perangkat Apple MacBook pejabat/tim kreatif',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Microsoft Office',
                'tipe_software_id'    => $tipeMap['Aplikasi perkantoran'] ?? null,
                'versi'               => '2019 Professional Plus',
                'penyedia_barang_id'  => $vendorMap['Microsoft Corporation'] ?? null,
                'kategori_id'         => $katMap['Lisensi'] ?? null,
                'keterangan'          => 'Aplikasi perkantoran pengolah kata, spreadsheet, dan presentasi',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Microsoft Office',
                'tipe_software_id'    => $tipeMap['Aplikasi perkantoran'] ?? null,
                'versi'               => '2021 / Microsoft 365 Apps',
                'penyedia_barang_id'  => $vendorMap['Microsoft Corporation'] ?? null,
                'kategori_id'         => $katMap['Lisensi'] ?? null,
                'keterangan'          => 'Suite aplikasi perkantoran resmi Pemdaprov Jabar',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Google Chrome',
                'tipe_software_id'    => $tipeMap['Browser'] ?? null,
                'versi'               => '128.0+ Enterprise',
                'penyedia_barang_id'  => $vendorMap['Google LLC'] ?? null,
                'kategori_id'         => $katMap['Open source'] ?? null,
                'keterangan'          => 'Peramban web standar akses aplikasi internal dan internet',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Mozilla Firefox ESR',
                'tipe_software_id'    => $tipeMap['Browser'] ?? null,
                'versi'               => '115.x Extended Support Release',
                'penyedia_barang_id'  => $vendorMap['Mozilla Foundation'] ?? null,
                'kategori_id'         => $katMap['Open source'] ?? null,
                'keterangan'          => 'Peramban alternatif untuk keamanan data dan stabilitas',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Visual Studio Code',
                'tipe_software_id'    => $tipeMap['Development Tool'] ?? null,
                'versi'               => '1.92+',
                'penyedia_barang_id'  => $vendorMap['Microsoft Corporation'] ?? null,
                'kategori_id'         => $katMap['Open source'] ?? null,
                'keterangan'          => 'Code editor resmi untuk tim pengembang aplikasi SPBE',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Postman',
                'tipe_software_id'    => $tipeMap['Development Tool'] ?? null,
                'versi'               => 'v11.x Enterprise',
                'penyedia_barang_id'  => $vendorMap['Postman Inc.'] ?? null,
                'kategori_id'         => $katMap['Lisensi'] ?? null,
                'keterangan'          => 'Alat pengujian integrasi API dan interoperabilitas',
                'bidang_id'           => 5,
            ],
            [
                'nama_software'       => 'Portal Layanan Terpadu Jabar',
                'tipe_software_id'    => $tipeMap['Web Application'] ?? null,
                'versi'               => 'v3.2.0-STABLE',
                'penyedia_barang_id'  => $vendorMap['Diskominfo Jawa Barat'] ?? null,
                'kategori_id'         => $katMap['In house'] ?? null,
                'keterangan'          => 'Aplikasi portal tata kelola layanan administrasi Diskominfo',
                'bidang_id'           => 5,
            ],
        ];

        // Ensure each initial software is present
        foreach ($initialSoftware as $s) {
            DB::table('smki_software_standars')->updateOrInsert(
                [
                    'nama_software' => $s['nama_software'],
                    'versi'         => $s['versi'],
                ],
                array_merge($s, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}

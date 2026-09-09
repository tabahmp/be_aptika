<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengisi data master acuan (lookup reference) standar untuk modul SMKI:
     * - Kategori Software (Lisensi, Open source, In house, Freeware)
     * - Tipe Software standar (Operating System, Office suite, Browser, dll.)
     * - Master Vendor terkemuka awal
     *
     * Catatan: Data ini BUKAN data dummy/sampel pengujian, melainkan referensi
     * master struktural agar opsi dropdown pada formulir inventarisasi langsung tersedia.
     */
    public function up(): void
    {
        // 1. Master Kategori Software
        if (Schema::hasTable('smki_kategoris')) {
            $kategoris = [
                ['nama_kategori' => 'Lisensi', 'keterangan' => 'Software berbayar/lisensi komersial resmi'],
                ['nama_kategori' => 'Open source', 'keterangan' => 'Software sumber terbuka dengan lisensi publik'],
                ['nama_kategori' => 'In house', 'keterangan' => 'Software/aplikasi mandiri hasil pengembangan internal organisasi'],
            ];

            foreach ($kategoris as $k) {
                DB::table('smki_kategoris')->updateOrInsert(
                    ['nama_kategori' => $k['nama_kategori']],
                    [
                        'keterangan' => $k['keterangan'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        // 2. Master Tipe Software
        if (Schema::hasTable('smki_tipe_softwares')) {
            $tipes = [
                ['nama_tipe_software' => 'Operating System', 'keterangan' => 'Sistem operasi perangkat keras'],
                ['nama_tipe_software' => 'Aplikasi perkantoran', 'keterangan' => 'Office productivity suite'],
                ['nama_tipe_software' => 'Web Application', 'keterangan' => 'Aplikasi berbasis peramban web'],
                ['nama_tipe_software' => 'Desktop Application', 'keterangan' => 'Aplikasi mandiri yang terpasang di komputer desktop'],
                ['nama_tipe_software' => 'Browser', 'keterangan' => 'Peramban web internet'],
                ['nama_tipe_software' => 'Development Tool', 'keterangan' => 'Alat pemrograman, IDE, compiler, dan database tool'],
                ['nama_tipe_software' => 'Communication', 'keterangan' => 'Perangkat komunikasi daring, chat, dan rapat virtual'],
                ['nama_tipe_software' => 'Security & Antivirus', 'keterangan' => 'Perangkat pengamanan, proteksi endpoint, dan firewall'],
                ['nama_tipe_software' => 'System Software', 'keterangan' => 'Perangkat lunak utilitas sistem & driver'],
                ['nama_tipe_software' => 'Database Management', 'keterangan' => 'Sistem manajemen basis data / DBMS'],
                ['nama_tipe_software' => 'Design & Multimedia', 'keterangan' => 'Aplikasi pengolah grafis, audio, dan video'],
            ];

            foreach ($tipes as $t) {
                DB::table('smki_tipe_softwares')->updateOrInsert(
                    ['nama_tipe_software' => $t['nama_tipe_software']],
                    [
                        'keterangan' => $t['keterangan'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        // 3. Master Vendor/Penyedia Populer
        if (Schema::hasTable('smki_penyedia_barangs')) {
            $vendors = [
                ['nama_penyedia_barang' => 'Microsoft Corporation', 'kontak_vendor' => 'support@microsoft.com'],
                ['nama_penyedia_barang' => 'Google LLC', 'kontak_vendor' => 'support@google.com'],
                ['nama_penyedia_barang' => 'Adobe Systems', 'kontak_vendor' => 'license@adobe.com'],
                ['nama_penyedia_barang' => 'Apple Inc.', 'kontak_vendor' => 'enterprise@apple.com'],
                ['nama_penyedia_barang' => 'Oracle Corporation', 'kontak_vendor' => 'support@oracle.com'],
                ['nama_penyedia_barang' => 'Canonical Ltd', 'kontak_vendor' => 'support@canonical.com'],
                ['nama_penyedia_barang' => 'The Document Foundation', 'kontak_vendor' => 'info@documentfoundation.org'],
            ];

            foreach ($vendors as $v) {
                DB::table('smki_penyedia_barangs')->updateOrInsert(
                    ['nama_penyedia_barang' => $v['nama_penyedia_barang']],
                    [
                        'kontak_vendor' => $v['kontak_vendor'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tetap dipertahankan untuk keamanan integritas foreign key
    }
};

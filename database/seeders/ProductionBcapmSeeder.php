<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ProductionBcapmSeeder
 *
 * Tujuan  : Inisialisasi awal tabel Berita Acara Penghancuran Media (BCAPM / FR-014)
 *           untuk environment Railway Production.
 * Prinsip : ZERO data dummy operasional. Memastikan tabel siap pakai, integritas referensi aman.
 * Aman    : Idempoten — dapat dijalankan berulang kali tanpa efek samping dan tidak menyentuh tabel lain.
 */
class ProductionBcapmSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Verifikasi tabel BCAPM sudah ada (migrasi sudah berjalan)
        if (!Schema::hasTable('berita_acara')) {
            if ($this->command) {
                $this->command->error('[ProductionBcapmSeeder] GAGAL: Tabel berita_acara tidak ditemukan. Pastikan migrasi sudah berjalan.');
            }
            return;
        }

        if (!Schema::hasTable('detail_media')) {
            if ($this->command) {
                $this->command->error('[ProductionBcapmSeeder] GAGAL: Tabel detail_media tidak ditemukan.');
            }
            return;
        }

        // 2. Guard: Jika sudah ada data transaksi operasional di Railway, lewati
        $existingCount = DB::table('berita_acara')
            ->whereNull('deleted_at')
            ->count();

        if ($existingCount > 0) {
            if ($this->command) {
                $this->command->info("[ProductionBcapmSeeder] Tabel `berita_acara` sudah berisi {$existingCount} data. Seeder dilewati — data produksi tetap terjaga utuh.");
            }
            return;
        }

        // 3. Status Siap: Data operasional diisi secara organik oleh staf/auditor di produksi.
        // Data demo/mockup dummy TIDAK dimasukkan ke environment Railway Production demi integritas data.
        if ($this->command) {
            $this->command->info('[ProductionBcapmSeeder] ✅ Tabel `berita_acara` dan `detail_media` siap digunakan.');
            $this->command->info('[ProductionBcapmSeeder] Tidak ada data dummy yang diinjeksi — data operasional akan dibuat oleh user.');
        }
    }
}

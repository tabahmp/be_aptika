<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ProductionDaftarRekamanSeeder
 *
 * Tujuan  : Inisialisasi tabel master lookup Formulir Daftar Rekaman (FR-003)
 *           untuk environment Railway Production.
 *
 * Prinsip : ZERO data dummy operasional.
 *           Hanya menyuntikkan data referensi master (klasifikasi, retensi, pemilik).
 *
 * Aman    : Idempoten — dapat dijalankan berulang kali tanpa efek samping.
 *           Tidak menyentuh tabel lain selain ketiga tabel master di bawah.
 *           Tabel smki_daftar_rekamans TIDAK diisi — data diisi organik oleh user.
 *
 * Dijalankan dengan:
 *   php artisan db:seed --class=ProductionDaftarRekamanSeeder
 */
class ProductionDaftarRekamanSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // GUARD 1: Verifikasi tabel sudah dibuat oleh migrasi
        // ─────────────────────────────────────────────────────────────────────
        $requiredTables = [
            'smki_rekaman_klasifikasis',
            'smki_rekaman_retensis',
            'smki_rekaman_pemiliks',
            'smki_daftar_rekamans',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->command?->error(
                    "[ProductionDaftarRekamanSeeder] GAGAL: Tabel `{$table}` tidak ditemukan. " .
                    "Pastikan migrasi 2026_09_19_000001_create_smki_daftar_rekamans_table.php sudah dijalankan."
                );
                return;
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // SEED 1: Master Klasifikasi Keamanan Rekaman
        // ─────────────────────────────────────────────────────────────────────
        $klasifikasiData = [
            ['nama_klasifikasi' => 'Umum'],
            ['nama_klasifikasi' => 'Terbatas'],
            ['nama_klasifikasi' => 'Rahasia'],
            ['nama_klasifikasi' => 'Sangat Rahasia'],
        ];

        $klasifikasiInserted = 0;
        foreach ($klasifikasiData as $item) {
            $exists = DB::table('smki_rekaman_klasifikasis')
                ->where('nama_klasifikasi', $item['nama_klasifikasi'])
                ->exists();

            if (!$exists) {
                DB::table('smki_rekaman_klasifikasis')->insert([
                    'nama_klasifikasi' => $item['nama_klasifikasi'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
                $klasifikasiInserted++;
            }
        }

        $totalKlasifikasi = DB::table('smki_rekaman_klasifikasis')->count();
        $this->command?->info(
            "[ProductionDaftarRekamanSeeder] ✅ Klasifikasi: {$klasifikasiInserted} ditambahkan, total {$totalKlasifikasi} data."
        );

        // ─────────────────────────────────────────────────────────────────────
        // SEED 2: Master Masa Retensi Dokumen
        // ─────────────────────────────────────────────────────────────────────
        $retensiData = [
            ['nama_retensi' => '1 Tahun'],
            ['nama_retensi' => '2 Tahun'],
            ['nama_retensi' => '3 Tahun'],
            ['nama_retensi' => '5 Tahun'],
            ['nama_retensi' => '10 Tahun'],
            ['nama_retensi' => 'Permanen'],
        ];

        $retensiInserted = 0;
        foreach ($retensiData as $item) {
            $exists = DB::table('smki_rekaman_retensis')
                ->where('nama_retensi', $item['nama_retensi'])
                ->exists();

            if (!$exists) {
                DB::table('smki_rekaman_retensis')->insert([
                    'nama_retensi' => $item['nama_retensi'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $retensiInserted++;
            }
        }

        $totalRetensi = DB::table('smki_rekaman_retensis')->count();
        $this->command?->info(
            "[ProductionDaftarRekamanSeeder] ✅ Retensi: {$retensiInserted} ditambahkan, total {$totalRetensi} data."
        );

        // ─────────────────────────────────────────────────────────────────────
        // SEED 3: Master Pemilik Rekaman (Unit/Bidang Internal)
        // ─────────────────────────────────────────────────────────────────────
        $pemilikData = [
            ['nama_pemilik' => 'Bagian Tata Usaha'],
            ['nama_pemilik' => 'Bidang Aptika'],
            ['nama_pemilik' => 'Seksi Sandikami'],
            ['nama_pemilik' => 'Seksi Infrastruktur'],
            ['nama_pemilik' => 'Seksi Tata Kelola'],
        ];

        $pemilikInserted = 0;
        foreach ($pemilikData as $item) {
            $exists = DB::table('smki_rekaman_pemiliks')
                ->where('nama_pemilik', $item['nama_pemilik'])
                ->exists();

            if (!$exists) {
                DB::table('smki_rekaman_pemiliks')->insert([
                    'nama_pemilik' => $item['nama_pemilik'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $pemilikInserted++;
            }
        }

        $totalPemilik = DB::table('smki_rekaman_pemiliks')->count();
        $this->command?->info(
            "[ProductionDaftarRekamanSeeder] ✅ Pemilik: {$pemilikInserted} ditambahkan, total {$totalPemilik} data."
        );

        // ─────────────────────────────────────────────────────────────────────
        // INFO: Tabel smki_daftar_rekamans TIDAK diisi data dummy
        // ─────────────────────────────────────────────────────────────────────
        $totalRekaman = DB::table('smki_daftar_rekamans')->whereNull('deleted_at')->count();
        $this->command?->info(
            "[ProductionDaftarRekamanSeeder] ℹ️  Tabel `smki_daftar_rekamans` saat ini berisi {$totalRekaman} data operasional."
        );
        $this->command?->info(
            "[ProductionDaftarRekamanSeeder] Data rekaman TIDAK diisi otomatis — user akan menambahkan secara organik."
        );
        $this->command?->info(
            "[ProductionDaftarRekamanSeeder] 🎉 Seeder selesai. Fitur Formulir Daftar Rekaman (FR-003) siap digunakan di Railway."
        );
    }
}

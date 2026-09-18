<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ProductionHardeningSeeder
 *
 * Tujuan  : Inisialisasi awal tabel Formulir Hardening untuk environment Railway Production.
 * Prinsip : ZERO data operasional. Hanya memastikan tabel siap pakai dan tidak ada data dummy.
 * Aman    : Idempoten — dapat dijalankan berulang kali tanpa efek samping.
 */
class ProductionHardeningSeeder extends Seeder
{
    public function run(): void
    {
        // Guard: Jika sudah ada data formulir (bukan dummy), hentikan seeder
        $existingCount = DB::table('smki_formulir_hardenings')
            ->whereNull('deleted_at')
            ->count();

        if ($existingCount > 0) {
            if ($this->command) {
                $this->command->info("[ProductionHardeningSeeder] Tabel sudah berisi {$existingCount} formulir. Seeder dilewati — tidak ada perubahan.");
            }
            return;
        }

        // Verifikasi tabel hardening sudah ada (migrasi sudah berjalan)
        if (! \Illuminate\Support\Facades\Schema::hasTable('smki_formulir_hardenings')) {
            if ($this->command) {
                $this->command->error('[ProductionHardeningSeeder] GAGAL: Tabel smki_formulir_hardenings tidak ditemukan. Pastikan migrasi sudah berjalan.');
            }
            return;
        }

        if (! \Illuminate\Support\Facades\Schema::hasTable('smki_formulir_hardening_checklists')) {
            if ($this->command) {
                $this->command->error('[ProductionHardeningSeeder] GAGAL: Tabel smki_formulir_hardening_checklists tidak ditemukan.');
            }
            return;
        }

        // Tabel siap, tidak ada data awal yang perlu dimasukkan.
        // Formulir Hardening adalah data OPERASIONAL yang diisi oleh pengguna di production.
        // Data awal (demo/mockup) TIDAK boleh masuk ke Railway Production.

        if ($this->command) {
            $this->command->info('[ProductionHardeningSeeder] ✅ Tabel smki_formulir_hardenings dan smki_formulir_hardening_checklists siap digunakan.');
            $this->command->info('[ProductionHardeningSeeder] Tidak ada data awal yang dimasukkan — formulir akan diisi oleh pengguna secara operasional.');
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AsetTiKlasifikasi;
use App\Models\AsetTiJenis;
use App\Models\AsetTiKategori;
use App\Models\AsetTiMerek;
use App\Models\AsetTiTipe;
use App\Models\AsetTiPenyedia;
use App\Models\AsetTiPenanggungJawab;
use Illuminate\Support\Facades\File;

/**
 * Seeder master lookup untuk Daftar Aset TI.
 * Mengisi data referensi (klasifikasi, jenis, kategori, merek, tipe, penyedia, penanggung jawab)
 * yang dibutuhkan dropdown pada form tambah/edit aset.
 * Aman dijalankan berulang kali (idempotent via firstOrCreate).
 */
class AsetTiMasterSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Baca data dari aset_data.json jika tersedia ────────────────
        $jsonPath = __DIR__ . '/aset_data.json';
        if (File::exists($jsonPath)) {
            $items = json_decode(File::get($jsonPath), true);
            if (is_array($items)) {
                foreach ($items as $row) {
                    if (!empty($row['klasifikasi'])) {
                        AsetTiKlasifikasi::firstOrCreate(['nama_klasifikasi' => trim($row['klasifikasi'])]);
                    }
                    if (!empty($row['jenis'])) {
                        AsetTiJenis::firstOrCreate(['nama_jenis' => trim($row['jenis'])]);
                    }
                    if (!empty($row['kategori'])) {
                        AsetTiKategori::firstOrCreate(['nama_kategori' => trim($row['kategori'])]);
                    }
                    if (!empty($row['merek'])) {
                        AsetTiMerek::firstOrCreate(['nama_merek' => trim($row['merek'])]);
                    }
                    if (!empty($row['tipe'])) {
                        AsetTiTipe::firstOrCreate(['nama_tipe' => trim($row['tipe'])]);
                    }
                    if (!empty($row['penyedia']) && trim($row['penyedia']) !== '-') {
                        AsetTiPenyedia::firstOrCreate(['nama_penyedia' => trim($row['penyedia'])]);
                    }
                    if (!empty($row['penanggung_jawab'])) {
                        AsetTiPenanggungJawab::firstOrCreate(['nama_pj' => trim($row['penanggung_jawab'])]);
                    }
                }
            }
        }

        // ── 2. Master Klasifikasi Default ──────────────────────────────────
        $klasifikasis = [
            'Terbatas/personal',
            'Internal Bidang',
            'Publik',
            'Rahasia',
        ];
        foreach ($klasifikasis as $nama) {
            AsetTiKlasifikasi::firstOrCreate(['nama_klasifikasi' => $nama]);
        }

        // ── 3. Master Jenis Default ────────────────────────────────────────
        $jeniss = [
            'hardware',
            'software',
            'jaringan',
            'layanan',
        ];
        foreach ($jeniss as $nama) {
            AsetTiJenis::firstOrCreate(['nama_jenis' => $nama]);
        }

        // ── 4. Master Kategori Default ─────────────────────────────────────
        $kategoris = [
            'PC/Monitor',
            'Note Book',
            'Laptop',
            'Cam',
            'Printer',
            'Router',
            'Monitor',
            'Televisi LED',
            'Email',
            'Scanner',
            'Network Switch',
            'UPS/Power',
            'Keyboard & Mouse',
            'Server',
        ];
        foreach ($kategoris as $nama) {
            AsetTiKategori::firstOrCreate(['nama_kategori' => $nama]);
        }

        // ── 5. Master Merek Default ────────────────────────────────────────
        $mereks = [
            'HP',
            'HP BUSINESS DESKTOP',
            'LENOVO IDEACENTRE',
            'Dell',
            'ASUS',
            'ASUS VIVOBOOK FLIP 14',
            'APPLE MacBook',
            'Apple',
            'Logitech',
            'Linksys',
            'Epson',
            'Samsung',
            'LG',
            'Fortinet',
            'Acer',
            'Toshiba',
            'Cisco',
            'TP-Link',
            'APC',
            'Canon',
        ];
        foreach ($mereks as $nama) {
            AsetTiMerek::firstOrCreate(['nama_merek' => $nama]);
        }

        // ── 6. Master Tipe Default ─────────────────────────────────────────
        $tipes = [
            'HP ProDesk 400GS MT',
            'IC510-15ICB [90HU00F1ID]',
            'ROG',
            'TP412FA-EC701T STAR GREY',
            'MacBook Pro Retina',
            'HP Laser jet P2055dn',
            'HP Laser Jet Pro MFP M130fn',
            'HP Laser Jet Pro MFP M130fw',
            'Epson L455',
            'Epson L3150',
            'Samsung 43 Inch',
            'LG 55 inch 55UM7100PTA',
            'LG 32 Inch 32LH500D-TA',
            'FML-400F (FortiCare dan FortiGuard Base Bundle)',
            'G10CE',
            'Travelmate',
            'ExpertBook',
            'ROG STRIX SCAR 15',
            'ExpertBook 7150w',
            'MacBook Pro M4 14"',
            'TUF',
            'Dell All in One',
            'HP All in One',
            'Lenovo ThinkPad',
            'Lenovo IdeaPad',
        ];
        foreach ($tipes as $nama) {
            AsetTiTipe::firstOrCreate(['nama_tipe' => $nama]);
        }

        // ── 7. Master Penyedia Default ─────────────────────────────────────
        $penyedias = [
            'PT. Bhineka',
            'PT. Net Sistem Infotama',
            'E-Katalog',
            'PT. Telkom Indonesia',
            'Pengadaan Langsung',
            'Hibah',
        ];
        foreach ($penyedias as $nama) {
            AsetTiPenyedia::firstOrCreate(['nama_penyedia' => $nama]);
        }

        // ── 8. Master Penanggung Jawab Default ─────────────────────────────
        $penanggungJawabs = [
            'Asep Junaedi',
            'Cony Trijulianto',
            'Dian Istanti',
            'Fajar Subakti',
            'Indri Koesnadi',
            'Staff Bidang Aptika',
            'Kepala Bidang Aptika',
        ];
        foreach ($penanggungJawabs as $nama) {
            AsetTiPenanggungJawab::firstOrCreate(['nama_pj' => $nama]);
        }

        if (isset($this->command)) {
            $this->command->info('Master data Aset TI (klasifikasi, jenis, kategori, merek, tipe, penyedia, penanggung jawab) berhasil di-seed.');
        }
    }
}

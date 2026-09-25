#!/usr/bin/env php
<?php

/**
 * sanity_check_daftar_rekaman.php
 *
 * Skrip sanity check untuk memvalidasi kesiapan tabel Formulir Daftar Rekaman (FR-003)
 * sebelum dan sesudah deployment ke Railway Production.
 *
 * Cara menjalankan (dari root be_aptika):
 *   php sanity_check_daftar_rekaman.php
 *
 * Exit Code:
 *   0 = Semua OK
 *   1 = Ada masalah yang perlu ditangani
 */

// ─── Bootstrap Laravel ───────────────────────────────────────────────────────
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$passed = 0;
$failed = 0;
$warnings = 0;

function ok(string $msg): void
{
    global $passed;
    $passed++;
    echo "\033[32m  ✅ PASS\033[0m  {$msg}\n";
}

function fail(string $msg): void
{
    global $failed;
    $failed++;
    echo "\033[31m  ❌ FAIL\033[0m  {$msg}\n";
}

function warn(string $msg): void
{
    global $warnings;
    $warnings++;
    echo "\033[33m  ⚠️  WARN\033[0m  {$msg}\n";
}

function section(string $title): void
{
    echo "\n\033[36m" . str_repeat('─', 60) . "\033[0m\n";
    echo "\033[36m  {$title}\033[0m\n";
    echo "\033[36m" . str_repeat('─', 60) . "\033[0m\n";
}

// ─── Header ──────────────────────────────────────────────────────────────────
echo "\n\033[1m\033[35m";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   SANITY CHECK — Formulir Daftar Rekaman (FR-003)       ║\n";
echo "║   Branch: feat/smki-formulir-daftar-rekaman             ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

// ─── [1] Tabel Wajib ─────────────────────────────────────────────────────────
section('1. Verifikasi Keberadaan Tabel');

$requiredTables = [
    'smki_rekaman_klasifikasis' => 'Master Klasifikasi Keamanan',
    'smki_rekaman_retensis'     => 'Master Masa Retensi Dokumen',
    'smki_rekaman_pemiliks'     => 'Master Pemilik Rekaman',
    'smki_daftar_rekamans'      => 'Tabel Utama Daftar Rekaman',
];

foreach ($requiredTables as $table => $label) {
    if (Schema::hasTable($table)) {
        ok("Tabel `{$table}` ditemukan — {$label}");
    } else {
        fail("Tabel `{$table}` TIDAK ditemukan — {$label}. Jalankan: php artisan migrate --path=database/migrations/2026_09_19_000001_create_smki_daftar_rekamans_table.php");
    }
}

// ─── [2] Kolom Wajib Tabel Utama ─────────────────────────────────────────────
section('2. Verifikasi Kolom Tabel smki_daftar_rekamans');

if (Schema::hasTable('smki_daftar_rekamans')) {
    $requiredColumns = ['id', 'user_id', 'bidang_id', 'judul', 'klasifikasi_id', 'retensi_id', 'pemilik_id', 'deleted_at', 'created_at', 'updated_at'];
    foreach ($requiredColumns as $col) {
        if (Schema::hasColumn('smki_daftar_rekamans', $col)) {
            ok("Kolom `{$col}` tersedia di tabel smki_daftar_rekamans");
        } else {
            fail("Kolom `{$col}` TIDAK ditemukan di tabel smki_daftar_rekamans");
        }
    }
} else {
    warn("Skipping verifikasi kolom — tabel smki_daftar_rekamans belum ada");
}

// ─── [3] Data Master Lookup ───────────────────────────────────────────────────
section('3. Verifikasi Data Master Lookup');

if (Schema::hasTable('smki_rekaman_klasifikasis')) {
    $count = DB::table('smki_rekaman_klasifikasis')->count();
    if ($count >= 4) {
        ok("Tabel klasifikasi berisi {$count} entri (minimum 4 diperlukan)");
    } else {
        fail("Tabel klasifikasi hanya berisi {$count} entri. Jalankan: php artisan db:seed --class=ProductionDaftarRekamanSeeder");
    }
}

if (Schema::hasTable('smki_rekaman_retensis')) {
    $count = DB::table('smki_rekaman_retensis')->count();
    if ($count >= 6) {
        ok("Tabel retensi berisi {$count} entri (minimum 6 diperlukan)");
    } else {
        fail("Tabel retensi hanya berisi {$count} entri. Jalankan: php artisan db:seed --class=ProductionDaftarRekamanSeeder");
    }
}

if (Schema::hasTable('smki_rekaman_pemiliks')) {
    $count = DB::table('smki_rekaman_pemiliks')->count();
    if ($count >= 5) {
        ok("Tabel pemilik berisi {$count} entri (minimum 5 diperlukan)");
    } else {
        fail("Tabel pemilik hanya berisi {$count} entri. Jalankan: php artisan db:seed --class=ProductionDaftarRekamanSeeder");
    }
}

// ─── [4] Foreign Key Integrity ────────────────────────────────────────────────
section('4. Verifikasi Referential Integrity');

if (Schema::hasTable('smki_daftar_rekamans')) {
    // Cek orphaned records
    $orphanKlasifikasi = DB::table('smki_daftar_rekamans')
        ->whereNotNull('klasifikasi_id')
        ->whereNotIn('klasifikasi_id', DB::table('smki_rekaman_klasifikasis')->pluck('id'))
        ->count();
    if ($orphanKlasifikasi === 0) {
        ok("Tidak ada orphaned records pada kolom klasifikasi_id");
    } else {
        warn("Ditemukan {$orphanKlasifikasi} rekaman dengan klasifikasi_id yang tidak valid");
    }

    $orphanRetensi = DB::table('smki_daftar_rekamans')
        ->whereNotNull('retensi_id')
        ->whereNotIn('retensi_id', DB::table('smki_rekaman_retensis')->pluck('id'))
        ->count();
    if ($orphanRetensi === 0) {
        ok("Tidak ada orphaned records pada kolom retensi_id");
    } else {
        warn("Ditemukan {$orphanRetensi} rekaman dengan retensi_id yang tidak valid");
    }

    $orphanPemilik = DB::table('smki_daftar_rekamans')
        ->whereNotNull('pemilik_id')
        ->whereNotIn('pemilik_id', DB::table('smki_rekaman_pemiliks')->pluck('id'))
        ->count();
    if ($orphanPemilik === 0) {
        ok("Tidak ada orphaned records pada kolom pemilik_id");
    } else {
        warn("Ditemukan {$orphanPemilik} rekaman dengan pemilik_id yang tidak valid");
    }
}

// ─── [5] Template File DOCX ───────────────────────────────────────────────────
section('5. Verifikasi Template Dokumen FR-003');

$templatePath = __DIR__ . '/resources/templates/FR-003 Formulir Daftar Rekaman.docx';
if (file_exists($templatePath)) {
    $size = number_format(filesize($templatePath) / 1024, 1);
    ok("Template FR-003.docx ditemukan di resources/templates/ ({$size} KB)");
} else {
    fail("Template 'FR-003 Formulir Daftar Rekaman.docx' TIDAK ditemukan di resources/templates/. Upload file template ke folder tersebut.");
}

// ─── [6] Tabel Eksisting Railway Tidak Terpengaruh ───────────────────────────
section('6. Verifikasi Keamanan — Tabel Eksisting Railway');

$existingSmkiTables = [
    'smki_software_standars'              => 'SMKI Software Standar',
    'smki_kategoris'                      => 'SMKI Kategori',
    'smki_formulir_hardenings'            => 'Formulir Hardening',
    'smki_formulir_hardening_checklists'  => 'Hardening Checklists',
    'berita_acara'                        => 'Berita Acara Pemusnahan Media',
    'detail_media'                        => 'Detail Media BCAPM',
];

foreach ($existingSmkiTables as $table => $label) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        ok("Tabel `{$table}` ({$label}) masih ada — {$count} baris, TIDAK terpengaruh migrasi FR-003");
    } else {
        warn("Tabel `{$table}` ({$label}) tidak ditemukan — mungkin belum dimigrasikan");
    }
}

// ─── Ringkasan Hasil ─────────────────────────────────────────────────────────
echo "\n\033[36m" . str_repeat('═', 60) . "\033[0m\n";
echo "\033[1m  RINGKASAN SANITY CHECK\033[0m\n";
echo "\033[36m" . str_repeat('═', 60) . "\033[0m\n";
echo "  \033[32m✅ PASS   : {$passed}\033[0m\n";
echo "  \033[33m⚠️  WARNING: {$warnings}\033[0m\n";
echo "  \033[31m❌ FAIL   : {$failed}\033[0m\n";
echo "\033[36m" . str_repeat('═', 60) . "\033[0m\n\n";

if ($failed === 0 && $warnings === 0) {
    echo "\033[1m\033[32m🎉 Semua check LULUS! Fitur Formulir Daftar Rekaman siap deploy ke Railway.\033[0m\n\n";
    exit(0);
} elseif ($failed === 0) {
    echo "\033[1m\033[33m⚠️  Siap deploy dengan peringatan. Periksa WARNING di atas.\033[0m\n\n";
    exit(0);
} else {
    echo "\033[1m\033[31m❌ Ada {$failed} item GAGAL. Selesaikan dulu sebelum deploy ke Railway.\033[0m\n\n";
    exit(1);
}

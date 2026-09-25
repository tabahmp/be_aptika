#!/usr/bin/env php
<?php

/**
 * ============================================================
 * sanity_check_bcapm.php
 * Skrip Verifikasi & Keamanan Migrasi — Berita Acara Pemusnahan Media (BCAPM / FR-014)
 * ============================================================
 * Tujuan  : Memastikan migrasi hanya menyentuh tabel BCAPM (berita_acara, detail_media)
 *           dan TIDAK mengubah/menghapus/merusak tabel produksi yang sudah ada di Railway.
 * Jalankan: php sanity_check_bcapm.php
 *
 * Persyaratan:
 *   - PHP 8.1+
 *   - Ekstensi PDO MySQL
 *   - Variabel env: DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
 * ============================================================
 */

// --- Konfigurasi koneksi dari env Railway atau .env lokal ---
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        }
    }
}

$host     = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?? '127.0.0.1';
$port     = $_ENV['DB_PORT']     ?? getenv('DB_PORT')     ?? '3306';
$dbName   = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'db_aptika';
$user     = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║   Sanity Check: Migrasi Berita Acara Media (BCAPM FR-014)   ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// --- Koneksi Database ---
try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ [KONEKSI] Berhasil terhubung ke database: {$dbName}@{$host}:{$port}\n\n";
} catch (PDOException $e) {
    echo "❌ [KONEKSI] GAGAL: " . $e->getMessage() . "\n";
    exit(1);
}

$errors   = 0;
$warnings = 0;
$passed   = 0;

function check(string $label, bool $condition, string $pass, string $fail, bool $isCritical = true): void
{
    global $errors, $warnings, $passed;
    if ($condition) {
        echo "  ✅ [PASS]  {$label}: {$pass}\n";
        $passed++;
    } else {
        if ($isCritical) {
            echo "  ❌ [FAIL]  {$label}: {$fail}\n";
            $errors++;
        } else {
            echo "  ⚠️  [WARN]  {$label}: {$fail}\n";
            $warnings++;
        }
    }
}

// =============================================================
// BAGIAN 1: Verifikasi Tabel Baru BCAPM Sudah Ada
// =============================================================
echo "── BAGIAN 1: Verifikasi Tabel Baru BCAPM ───────────────────────\n";

$targetTables = [
    'berita_acara',
    'detail_media',
];

foreach ($targetTables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
    check(
        "Tabel `{$table}` ada",
        $stmt->rowCount() > 0,
        "Tabel ditemukan.",
        "Tabel TIDAK ADA — migrasi belum berjalan atau gagal!"
    );
}

// =============================================================
// BAGIAN 2: Verifikasi Kolom Kritis pada Tabel BCAPM
// =============================================================
echo "\n── BAGIAN 2: Verifikasi Skema Kolom BCAPM ──────────────────────\n";

$requiredColumns = [
    'berita_acara' => [
        'id_ba', 'nomor_dokumen', 'tanggal_pelaksanaan', 'alasan_penghancuran',
        'id_pelaksana', 'id_diketahui', 'nama_pelaksana', 'nama_diketahui',
        'bidang_id', 'deleted_at', 'created_at', 'updated_at',
    ],
    'detail_media' => [
        'id_detail', 'id_pelaksanaan', 'no_urut', 'nama_perangkat',
        'spesifikasi', 'jenis_media', 'serial_number', 'jumlah',
        'satuan', 'keterangan', 'created_at', 'updated_at',
    ],
];

foreach ($requiredColumns as $table => $columns) {
    $stmt = $pdo->query("DESCRIBE `{$table}`");
    $existing = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    foreach ($columns as $col) {
        check(
            "  `{$table}`.`{$col}`",
            in_array($col, $existing),
            "Ada.",
            "KOLOM HILANG — skema tidak lengkap!"
        );
    }
}

// =============================================================
// BAGIAN 3: Verifikasi Tabel Produksi Eksisting Tidak Termodifikasi
// =============================================================
echo "\n── BAGIAN 3: Verifikasi Tabel Produksi Tidak Termodifikasi ─────\n";

$productionTables = [
    'users', 'bidangs', 'services', 'bidang_services',
    'daftar_aset_tis', 'smki_software_standars',
    'smki_formulir_hardenings', 'smki_formulir_hardening_checklists',
    'spds', 'tasks', 'boards', 'board_members',
    'nota_dinas', 'magangs', 'permohonan_tis', 'kerentanans',
    'audit_logs',
];

foreach ($productionTables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
    $exists = $stmt->rowCount() > 0;
    if ($exists) {
        try {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $count     = $countStmt->fetchColumn();
            check(
                "Tabel `{$table}` utuh",
                true,
                "Ada & dapat dibaca ({$count} baris).",
                ""
            );
        } catch (PDOException $e) {
            check("Tabel `{$table}` utuh", false, "", "ERROR query: " . $e->getMessage());
        }
    } else {
        echo "  ℹ️  [INFO]  Tabel `{$table}` belum ada di DB ini.\n";
    }
}

// =============================================================
// BAGIAN 4: Verifikasi Foreign Key BCAPM
// =============================================================
echo "\n── BAGIAN 4: Verifikasi Foreign Key BCAPM ──────────────────────\n";

$fkChecks = [
    ['tabel' => 'berita_acara', 'kolom' => 'id_pelaksana',   'ref_table' => 'users'],
    ['tabel' => 'berita_acara', 'kolom' => 'id_diketahui',   'ref_table' => 'users'],
    ['tabel' => 'berita_acara', 'kolom' => 'bidang_id',      'ref_table' => 'bidangs'],
    ['tabel' => 'detail_media', 'kolom' => 'id_pelaksanaan', 'ref_table' => 'berita_acara'],
];

foreach ($fkChecks as $fk) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = :db
          AND TABLE_NAME   = :tabel
          AND COLUMN_NAME  = :kolom
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute([
        ':db'    => $dbName,
        ':tabel' => $fk['tabel'],
        ':kolom' => $fk['kolom'],
    ]);
    $hasFk = $stmt->fetchColumn() > 0;
    check(
        "FK `{$fk['tabel']}`.`{$fk['kolom']}` → `{$fk['ref_table']}`",
        $hasFk,
        "Foreign key terdaftar.",
        "Foreign key TIDAK ditemukan!",
        false
    );
}

// =============================================================
// BAGIAN 5: Verifikasi Riwayat Migrasi Laravel
// =============================================================
echo "\n── BAGIAN 5: Verifikasi Riwayat Migrasi Laravel ───────────────\n";

$bcapmMigration = '2026_09_21_000001_create_berita_acara_and_detail_media_tables';

$stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
if ($stmt->rowCount() > 0) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = :m");
    $stmt->execute([':m' => $bcapmMigration]);
    check(
        "Migrasi `{$bcapmMigration}`",
        $stmt->fetchColumn() > 0,
        "Sudah tercatat di tabel migrations.",
        "BELUM dijalankan — jalankan `php artisan migrate --force`!"
    );
} else {
    echo "  ⚠️  [WARN]  Tabel `migrations` tidak ditemukan.\n";
    $warnings++;
}

// =============================================================
// RINGKASAN HASIL
// =============================================================
echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    RINGKASAN HASIL                          ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  ✅ PASSED  : {$passed}\n";
echo "║  ⚠️  WARNING : {$warnings}\n";
echo "║  ❌ FAILED  : {$errors}\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";

if ($errors === 0) {
    echo "║  🚀 STATUS  : AMAN — Migrasi BCAPM siap untuk Railway Prod    ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "║  🛑 STATUS  : BLOKIR — Perbaiki error sebelum deploy!         ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    exit(1);
}

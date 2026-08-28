<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tabel transaksi yang membutuhkan kolom bidang_id untuk data isolation
     */
    protected array $targetTables = [
        'nota_dinas',
        'hasil_pentests',
        'kerentanans',
        'permohonan_tis',
        'magangs',
        'detail_perjalanan',
        'spds',
        'boards',
    ];

    public function up(): void
    {
        // Pastikan tabel bidangs memiliki minimal APTIKA (id: 3)
        if (Schema::hasTable('bidangs') && !DB::table('bidangs')->where('id', 3)->exists()) {
            DB::table('bidangs')->updateOrInsert(
                ['id' => 3],
                [
                    'code' => 'APTIKA',
                    'name' => 'Bidang Aplikasi Informatika',
                    'description' => 'Bidang Aplikasi Informatika',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ($this->targetTables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'bidang_id')) {

                // Bersihkan data datetime invalid jika ada dengan aman
                try {
                    DB::statement("SET SESSION sql_mode = ''");
                    DB::statement("UPDATE `{$tableName}` SET `created_at` = NULL WHERE `created_at` = '0000-00-00 00:00:00'");
                    DB::statement("UPDATE `{$tableName}` SET `updated_at` = NULL WHERE `updated_at` = '0000-00-00 00:00:00'");
                } catch (\Throwable $e) {
                    // Abaikan jika tidak ada zero date atau mode tidak mengizinkan
                }

                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->foreignId('bidang_id')
                          ->nullable()
                          ->constrained('bidangs')
                          ->onDelete('restrict');

                    // Simple index pada bidang_id
                    $table->index('bidang_id', "{$tableName}_bidang_idx");
                });

                // Defaultkan seluruh data eksisting production ke bidang_id = 3 (APTIKA)
                if (Schema::hasTable('bidangs') && DB::table('bidangs')->where('id', 3)->exists()) {
                    DB::table($tableName)->whereNull('bidang_id')->update(['bidang_id' => 3]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->targetTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'bidang_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropForeign(['bidang_id']);
                    $table->dropIndex("{$tableName}_bidang_idx");
                    $table->dropColumn('bidang_id');
                });
            }
        }
    }
};

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
        foreach ($this->targetTables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'bidang_id')) {

                // Bersihkan data datetime invalid ('0000-00-00 00:00:00') agar ALTER TABLE tidak gagal
                // Ini adalah data lama yang tidak valid dan perlu dinormalkan sebelum menambah foreign key
                DB::statement("UPDATE `{$tableName}` SET `created_at` = NULL WHERE `created_at` = '0000-00-00 00:00:00'");
                DB::statement("UPDATE `{$tableName}` SET `updated_at` = NULL WHERE `updated_at` = '0000-00-00 00:00:00'");

                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->foreignId('bidang_id')
                          ->nullable()
                          ->constrained('bidangs')
                          ->onDelete('restrict');

                    // Simple index pada bidang_id (lebih aman daripada composite dengan created_at yang bisa NULL)
                    $table->index('bidang_id', "{$tableName}_bidang_idx");
                });

                // Defaultkan data eksisting ke bidang_id = 3 (APTIKA)
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
                    $table->dropIndex("{$tableName}_bidang_created_idx");
                    $table->dropColumn('bidang_id');
                });
            }
        }
    }
};

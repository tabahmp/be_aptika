<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kerentanans', function (Blueprint $table) {
            if (!Schema::hasColumn('kerentanans', 'isi_lampiran')) {
                $table->text('isi_lampiran')->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('kerentanans', 'lampiran')) {
                $table->string('lampiran')->nullable()->after('isi_lampiran');
            }
            if (!Schema::hasColumn('kerentanans', 'lampiran_nama')) {
                $table->string('lampiran_nama')->nullable()->after('lampiran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kerentanans', function (Blueprint $table) {
            $colsToDrop = array_filter(['isi_lampiran', 'lampiran', 'lampiran_nama'], function ($col) {
                return Schema::hasColumn('kerentanans', $col);
            });
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};

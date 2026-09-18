<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smki_formulir_hardenings', function (Blueprint $table) {
            // Jadikan nullable karena form memperbolehkan simpan tanpa semua field terisi
            $table->date('tanggal_check')->nullable()->change();
            $table->string('jenis_aset')->nullable()->default(null)->change();
            $table->string('status')->nullable()->default(null)->change();
            $table->string('kota')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('smki_formulir_hardenings', function (Blueprint $table) {
            $table->date('tanggal_check')->nullable(false)->change();
            $table->string('jenis_aset')->nullable(false)->default('Laptop/PC')->change();
            $table->string('status')->nullable(false)->default('Dalam Proses')->change();
            $table->string('kota')->nullable(false)->default('Bandung')->change();
        });
    }
};

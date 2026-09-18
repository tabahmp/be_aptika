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
        // 1. Tabel Utama Formulir Hardening (FR-047)
        if (!Schema::hasTable('smki_formulir_hardenings')) {
            Schema::create('smki_formulir_hardenings', function (Blueprint $table) {
                $table->id();
                $table->string('no_dokumen')->unique()->index();
                
                // Relasi opsional ke daftar_aset_tis (jika memilih dari master aset)
                $table->unsignedBigInteger('aset_id')->nullable()->index();
                $table->string('nomor_aset')->nullable()->index();
                $table->string('jenis_aset')->default('Laptop/PC')->index();
                $table->string('merek_tipe')->nullable();
                $table->string('lokasi')->nullable();
                $table->date('tanggal_check');
                $table->string('status')->default('Dalam Proses')->index(); // Selesai, Dalam Proses, Menunggu, Draft

                // Pengesahan Auditor
                $table->string('kota')->default('Bandung');
                $table->date('tanggal_pengesahan')->nullable();
                $table->string('nama_auditor')->nullable();
                $table->string('nip_auditor')->nullable();
                $table->string('jabatan_auditor')->nullable();

                // Ringkasan Kepatuhan (Compliance Summary)
                $table->decimal('compliance_rate', 5, 2)->default(0.00);
                $table->unsignedSmallInteger('items_passed')->default(0);
                $table->unsignedSmallInteger('items_failed')->default(0);

                // Multi-tenancy & User
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();

                $table->softDeletes();
                $table->timestamps();

                // Foreign Keys
                $table->foreign('aset_id')->references('id')->on('daftar_aset_tis')->nullOnDelete();
                $table->foreign('bidang_id')->references('id')->on('bidangs')->nullOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 2. Tabel Rincian Checklist Hardening per Formulir
        if (!Schema::hasTable('smki_formulir_hardening_checklists')) {
            Schema::create('smki_formulir_hardening_checklists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('formulir_hardening_id')->index();
                $table->string('kategori')->index(); // Sistem Operasi, Perlindungan Kata Sandi, dll.
                $table->text('item_pengecekan');
                $table->unsignedSmallInteger('urutan')->default(1);
                $table->string('checklist', 10)->default('Pass'); // Pass, Fail
                $table->text('keterangan')->nullable(); // Keterangan/Observasi

                $table->timestamps();

                $table->foreign('formulir_hardening_id')
                    ->references('id')
                    ->on('smki_formulir_hardenings')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smki_formulir_hardening_checklists');
        Schema::dropIfExists('smki_formulir_hardenings');
    }
};

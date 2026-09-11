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
        // 1. Master Tabel Nama Aset
        if (!Schema::hasTable('aset_ti_namas')) {
            Schema::create('aset_ti_namas', function (Blueprint $table) {
                $table->id();
                $table->string('nama_aset')->unique();
                $table->timestamps();
            });
        }

        // 2. Master Tabel Klasifikasi (Terbatas/personal, Publik, Rahasia, dll.)
        if (!Schema::hasTable('aset_ti_klasifikasis')) {
            Schema::create('aset_ti_klasifikasis', function (Blueprint $table) {
                $table->id();
                $table->string('nama_klasifikasi')->unique();
                $table->timestamps();
            });
        }

        // 3. Master Tabel Jenis (hardware, software, dll.)
        if (!Schema::hasTable('aset_ti_jeniss')) {
            Schema::create('aset_ti_jeniss', function (Blueprint $table) {
                $table->id();
                $table->string('nama_jenis')->unique();
                $table->timestamps();
            });
        }

        // 4. Master Tabel Kategori (PC/Monitor, Laptop, Printer, Scanner, Network, Power, Router, Televisi LED, dll.)
        if (!Schema::hasTable('aset_ti_kategoris')) {
            Schema::create('aset_ti_kategoris', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kategori')->unique();
                $table->timestamps();
            });
        }

        // 5. Master Tabel Merek (HP, LENOVO, ASUS, APPLE, DELL, CISCO, APC, FUJITSU, dll.)
        if (!Schema::hasTable('aset_ti_mereks')) {
            Schema::create('aset_ti_mereks', function (Blueprint $table) {
                $table->id();
                $table->string('nama_merek')->unique();
                $table->timestamps();
            });
        }

        // 6. Master Tabel Tipe Aset
        if (!Schema::hasTable('aset_ti_tipes')) {
            Schema::create('aset_ti_tipes', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tipe')->unique();
                $table->timestamps();
            });
        }

        // 7. Master Tabel Spesifikasi Teknis
        if (!Schema::hasTable('aset_ti_spesifikasis')) {
            Schema::create('aset_ti_spesifikasis', function (Blueprint $table) {
                $table->id();
                $table->text('spesifikasi_teknis');
                $table->timestamps();
            });
        }

        // 8. Master Tabel Pemanfaatan
        if (!Schema::hasTable('aset_ti_pemanfaatans')) {
            Schema::create('aset_ti_pemanfaatans', function (Blueprint $table) {
                $table->id();
                $table->text('pemanfaatan');
                $table->timestamps();
            });
        }

        // 9. Master Tabel Penyedia / Vendor (PT. Bhinneka, E-Katalog, PT. Telkom, dll.)
        if (!Schema::hasTable('aset_ti_penyedias')) {
            Schema::create('aset_ti_penyedias', function (Blueprint $table) {
                $table->id();
                $table->string('nama_penyedia')->unique();
                $table->timestamps();
            });
        }

        // 10. Master Tabel Penanggung Jawab
        if (!Schema::hasTable('aset_ti_penanggung_jawabs')) {
            Schema::create('aset_ti_penanggung_jawabs', function (Blueprint $table) {
                $table->id();
                $table->string('nama_pj')->unique();
                $table->timestamps();
            });
        }

        // 11. Tabel Utama: Daftar Aset TI
        if (!Schema::hasTable('daftar_aset_tis')) {
            Schema::create('daftar_aset_tis', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();

                $table->string('kode')->nullable()->index();
                $table->unsignedBigInteger('nama_aset_id')->nullable()->index();
                $table->string('nama_aset');

                $table->unsignedBigInteger('klasifikasi_id')->nullable()->index();
                $table->unsignedBigInteger('jenis_id')->nullable()->index();
                $table->unsignedBigInteger('kategori_id')->nullable()->index();
                $table->string('no_seri')->nullable()->index();

                $table->unsignedBigInteger('merek_id')->nullable()->index();
                $table->unsignedBigInteger('tipe_id')->nullable()->index();
                $table->unsignedBigInteger('spesifikasi_id')->nullable()->index();
                $table->text('spesifikasi_teknis')->nullable();

                $table->unsignedBigInteger('pemanfaatan_id')->nullable()->index();
                $table->text('pemanfaatan')->nullable();

                $table->unsignedBigInteger('penyedia_id')->nullable()->index();
                $table->string('tahun_pembelian', 10)->nullable();
                $table->string('garansi')->nullable();
                $table->string('date_end')->nullable(); // Batas Akhir Layanan Dukungan (End of Support)
                $table->string('tanggal_akhir_masa_pakai')->nullable(); // Batas Masa Pakai Produk (End of Life)

                $table->unsignedBigInteger('penanggung_jawab_id')->nullable()->index();
                $table->string('lokasi')->nullable()->index();

                $table->softDeletes();
                $table->timestamps();

                // Foreign keys
                $table->foreign('nama_aset_id')->references('id')->on('aset_ti_namas')->nullOnDelete();
                $table->foreign('klasifikasi_id')->references('id')->on('aset_ti_klasifikasis')->nullOnDelete();
                $table->foreign('jenis_id')->references('id')->on('aset_ti_jeniss')->nullOnDelete();
                $table->foreign('kategori_id')->references('id')->on('aset_ti_kategoris')->nullOnDelete();
                $table->foreign('merek_id')->references('id')->on('aset_ti_mereks')->nullOnDelete();
                $table->foreign('tipe_id')->references('id')->on('aset_ti_tipes')->nullOnDelete();
                $table->foreign('spesifikasi_id')->references('id')->on('aset_ti_spesifikasis')->nullOnDelete();
                $table->foreign('pemanfaatan_id')->references('id')->on('aset_ti_pemanfaatans')->nullOnDelete();
                $table->foreign('penyedia_id')->references('id')->on('aset_ti_penyedias')->nullOnDelete();
                $table->foreign('penanggung_jawab_id')->references('id')->on('aset_ti_penanggung_jawabs')->nullOnDelete();
                $table->foreign('bidang_id')->references('id')->on('bidangs')->nullOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_aset_tis');
        Schema::dropIfExists('aset_ti_penanggung_jawabs');
        Schema::dropIfExists('aset_ti_penyedias');
        Schema::dropIfExists('aset_ti_pemanfaatans');
        Schema::dropIfExists('aset_ti_spesifikasis');
        Schema::dropIfExists('aset_ti_tipes');
        Schema::dropIfExists('aset_ti_mereks');
        Schema::dropIfExists('aset_ti_kategoris');
        Schema::dropIfExists('aset_ti_jeniss');
        Schema::dropIfExists('aset_ti_klasifikasis');
        Schema::dropIfExists('aset_ti_namas');
    }
};

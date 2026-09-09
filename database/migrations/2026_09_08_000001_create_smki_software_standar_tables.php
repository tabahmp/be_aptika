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
        // 1. Tabel Kategori Software (Lisensi, Open source, In house, dll.)
        if (!Schema::hasTable('smki_kategoris')) {
            Schema::create('smki_kategoris', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kategori')->unique();
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Tipe Software (Operating System, Aplikasi Perkantoran, Web App, dll.)
        if (!Schema::hasTable('smki_tipe_softwares')) {
            Schema::create('smki_tipe_softwares', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tipe_software')->unique();
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tabel Penyedia Barang / Vendor (Microsoft Corporation, Adobe, Google, dll.)
        if (!Schema::hasTable('smki_penyedia_barangs')) {
            Schema::create('smki_penyedia_barangs', function (Blueprint $table) {
                $table->id();
                $table->string('nama_penyedia_barang')->unique();
                $table->string('kontak_vendor')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tabel Utama: Daftar Software Standar SMKI
        if (!Schema::hasTable('smki_software_standars')) {
            Schema::create('smki_software_standars', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('nama_software');
                $table->unsignedBigInteger('tipe_software_id')->nullable()->index();
                $table->string('versi')->nullable();
                $table->unsignedBigInteger('penyedia_barang_id')->nullable()->index();
                $table->unsignedBigInteger('kategori_id')->nullable()->index();
                $table->text('keterangan')->nullable();
                $table->softDeletes();
                $table->timestamps();

                // Foreign key constraints
                $table->foreign('tipe_software_id')->references('id')->on('smki_tipe_softwares')->nullOnDelete();
                $table->foreign('penyedia_barang_id')->references('id')->on('smki_penyedia_barangs')->nullOnDelete();
                $table->foreign('kategori_id')->references('id')->on('smki_kategoris')->nullOnDelete();
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
        Schema::dropIfExists('smki_software_standars');
        Schema::dropIfExists('smki_penyedia_barangs');
        Schema::dropIfExists('smki_tipe_softwares');
        Schema::dropIfExists('smki_kategoris');
    }
};

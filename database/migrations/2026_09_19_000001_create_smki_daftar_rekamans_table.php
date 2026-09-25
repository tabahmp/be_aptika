<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Klasifikasi Rekaman
        if (!Schema::hasTable('smki_rekaman_klasifikasis')) {
            Schema::create('smki_rekaman_klasifikasis', function (Blueprint $table) {
                $table->id();
                $table->string('nama_klasifikasi')->unique();
                $table->timestamps();
            });

            DB::table('smki_rekaman_klasifikasis')->insert([
                ['nama_klasifikasi' => 'Umum', 'created_at' => now(), 'updated_at' => now()],
                ['nama_klasifikasi' => 'Terbatas', 'created_at' => now(), 'updated_at' => now()],
                ['nama_klasifikasi' => 'Rahasia', 'created_at' => now(), 'updated_at' => now()],
                ['nama_klasifikasi' => 'Sangat Rahasia', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Master Retensi Rekaman
        if (!Schema::hasTable('smki_rekaman_retensis')) {
            Schema::create('smki_rekaman_retensis', function (Blueprint $table) {
                $table->id();
                $table->string('nama_retensi')->unique();
                $table->timestamps();
            });

            DB::table('smki_rekaman_retensis')->insert([
                ['nama_retensi' => '1 Tahun', 'created_at' => now(), 'updated_at' => now()],
                ['nama_retensi' => '2 Tahun', 'created_at' => now(), 'updated_at' => now()],
                ['nama_retensi' => '3 Tahun', 'created_at' => now(), 'updated_at' => now()],
                ['nama_retensi' => '5 Tahun', 'created_at' => now(), 'updated_at' => now()],
                ['nama_retensi' => '10 Tahun', 'created_at' => now(), 'updated_at' => now()],
                ['nama_retensi' => 'Permanen', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 3. Master Pemilik Rekaman
        if (!Schema::hasTable('smki_rekaman_pemiliks')) {
            Schema::create('smki_rekaman_pemiliks', function (Blueprint $table) {
                $table->id();
                $table->string('nama_pemilik')->unique();
                $table->timestamps();
            });

            DB::table('smki_rekaman_pemiliks')->insert([
                ['nama_pemilik' => 'Bagian Tata Usaha', 'created_at' => now(), 'updated_at' => now()],
                ['nama_pemilik' => 'Bidang Aptika', 'created_at' => now(), 'updated_at' => now()],
                ['nama_pemilik' => 'Seksi Sandikami', 'created_at' => now(), 'updated_at' => now()],
                ['nama_pemilik' => 'Seksi Infrastruktur', 'created_at' => now(), 'updated_at' => now()],
                ['nama_pemilik' => 'Seksi Tata Kelola', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 4. Tabel Utama SMKI Daftar Rekaman
        if (!Schema::hasTable('smki_daftar_rekamans')) {
            Schema::create('smki_daftar_rekamans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('bidang_id')->nullable()->index();

                $table->string('judul');
                $table->unsignedBigInteger('klasifikasi_id')->nullable()->index();
                $table->unsignedBigInteger('retensi_id')->nullable()->index();
                $table->unsignedBigInteger('pemilik_id')->nullable()->index();

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('bidang_id')->references('id')->on('bidangs')->nullOnDelete();
                $table->foreign('klasifikasi_id')->references('id')->on('smki_rekaman_klasifikasis')->nullOnDelete();
                $table->foreign('retensi_id')->references('id')->on('smki_rekaman_retensis')->nullOnDelete();
                $table->foreign('pemilik_id')->references('id')->on('smki_rekaman_pemiliks')->nullOnDelete();
            });

            // Sample data
            DB::table('smki_daftar_rekamans')->insert([
                [
                    'judul' => 'Surat Masuk',
                    'klasifikasi_id' => 1, // Umum
                    'retensi_id' => 4,     // 5 Tahun
                    'pemilik_id' => 1,     // Bagian Tata Usaha
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'judul' => 'Laporan Audit Kemanan Informasi',
                    'klasifikasi_id' => 3, // Rahasia
                    'retensi_id' => 5,     // 10 Tahun
                    'pemilik_id' => 3,     // Seksi Sandikami
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'judul' => 'Dokumen Penilaian Risiko TI',
                    'klasifikasi_id' => 2, // Terbatas
                    'retensi_id' => 4,     // 5 Tahun
                    'pemilik_id' => 5,     // Seksi Tata Kelola
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smki_daftar_rekamans');
        Schema::dropIfExists('smki_rekaman_pemiliks');
        Schema::dropIfExists('smki_rekaman_retensis');
        Schema::dropIfExists('smki_rekaman_klasifikasis');
    }
};

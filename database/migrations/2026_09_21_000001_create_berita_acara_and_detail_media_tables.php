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
        // 1. Tabel Utama: Berita Acara Penghancuran Media
        if (!Schema::hasTable('berita_acara')) {
            Schema::create('berita_acara', function (Blueprint $table) {
                $table->increments('id_ba'); // INT Primary Key, Auto-increment
                $table->string('nomor_dokumen', 100)->nullable()->index(); // e.g. BA-001/SMKI/2025
                $table->date('tanggal_pelaksanaan'); // Execution date
                $table->text('alasan_penghancuran'); // Reason for destruction
                $table->unsignedBigInteger('id_pelaksana')->nullable()->index(); // FK - Executor user ID
                $table->unsignedBigInteger('id_diketahui')->nullable()->index(); // FK - Approver user ID
                $table->string('nama_pelaksana', 255)->nullable(); // Fallback / Custom display name
                $table->string('nama_diketahui', 255)->nullable(); // Fallback / Custom display name
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
                $table->softDeletes(); // For Data Dihapus / Audit Trackers
                $table->timestamps();

                // Foreign key constraints if target tables exist
                if (Schema::hasTable('users')) {
                    $table->foreign('id_pelaksana')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('id_diketahui')->references('id')->on('users')->nullOnDelete();
                }
                if (Schema::hasTable('bidangs')) {
                    $table->foreign('bidang_id')->references('id')->on('bidangs')->nullOnDelete();
                }
            });
        }

        // 2. Tabel Rincian: Detail Media (One-to-Many dengan Berita Acara)
        if (!Schema::hasTable('detail_media')) {
            Schema::create('detail_media', function (Blueprint $table) {
                $table->increments('id_detail'); // INT Primary Key, Auto-increment
                $table->unsignedInteger('id_pelaksanaan'); // Foreign Key referencing berita_acara.id_ba
                $table->integer('no_urut')->default(1);
                $table->string('nama_perangkat', 255); // Device name e.g. Harddisk, Flashdisk
                $table->string('spesifikasi', 255)->nullable(); // e.g. Seagate Barracuda 1 TB, Intel Core i5
                $table->string('jenis_media', 100)->nullable(); // e.g. Storage, Laptop, PC
                $table->string('serial_number', 255)->nullable(); // Serial Number
                $table->integer('jumlah')->default(1); // Quantity
                $table->string('satuan', 50)->default('Unit'); // e.g. Unit, Buah
                $table->text('keterangan')->nullable(); // Remarks / Notes
                $table->timestamps();

                // Cascading delete: menghapus berita_acara akan menghapus seluruh detail_media terkait
                $table->foreign('id_pelaksanaan')
                    ->references('id_ba')
                    ->on('berita_acara')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_media');
        Schema::dropIfExists('berita_acara');
    }
};

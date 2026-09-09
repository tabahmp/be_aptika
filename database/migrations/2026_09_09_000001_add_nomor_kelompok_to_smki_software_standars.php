<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smki_software_standars', function (Blueprint $table) {
            // Nomor kelompok: satu nomor bisa memiliki banyak baris software
            // Misal: No. 1 = Microsoft Windows (dengan baris versi 8, 10, 11 terpisah)
            if (!Schema::hasColumn('smki_software_standars', 'nomor_kelompok')) {
                $table->unsignedInteger('nomor_kelompok')->nullable()->after('user_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('smki_software_standars', function (Blueprint $table) {
            $table->dropColumn('nomor_kelompok');
        });
    }
};

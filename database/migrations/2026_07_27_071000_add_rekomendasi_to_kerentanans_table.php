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
            if (!Schema::hasColumn('kerentanans', 'rekomendasi')) {
                $table->longText('rekomendasi')->nullable()->after('isi_lampiran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kerentanans', function (Blueprint $table) {
            if (Schema::hasColumn('kerentanans', 'rekomendasi')) {
                $table->dropColumn('rekomendasi');
            }
        });
    }
};

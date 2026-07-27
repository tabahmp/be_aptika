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
            $table->string('lampiran')->nullable()->after('deskripsi');
            $table->string('lampiran_nama')->nullable()->after('lampiran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kerentanans', function (Blueprint $table) {
            $table->dropColumn(['lampiran', 'lampiran_nama']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smki_formulir_hardenings', function (Blueprint $table) {
            if (!Schema::hasColumn('smki_formulir_hardenings', 'no_revisi')) {
                $table->string('no_revisi', 50)->nullable()->default('0.0')->after('no_dokumen');
            }
            if (!Schema::hasColumn('smki_formulir_hardenings', 'tanggal_terbit')) {
                $table->date('tanggal_terbit')->nullable()->after('no_revisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('smki_formulir_hardenings', function (Blueprint $table) {
            $table->dropColumn(['no_revisi', 'tanggal_terbit']);
        });
    }
};

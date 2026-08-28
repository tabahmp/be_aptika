<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bidang_id')) {
                $table->foreignId('bidang_id')
                      ->nullable()
                      ->after('role')
                      ->constrained('bidangs')
                      ->onDelete('restrict');

                $table->index(['bidang_id', 'role', 'is_active'], 'users_bidang_role_active_idx');
            }
        });

        // Set default bidang_id = 3 (APTIKA) untuk user eksisting yang belum terisi
        if (Schema::hasTable('bidangs') && DB::table('bidangs')->where('id', 3)->exists()) {
            DB::table('users')->whereNull('bidang_id')->update(['bidang_id' => 3]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'bidang_id')) {
                $table->dropForeign(['bidang_id']);
                $table->dropIndex('users_bidang_role_active_idx');
                $table->dropColumn('bidang_id');
            }
        });
    }
};

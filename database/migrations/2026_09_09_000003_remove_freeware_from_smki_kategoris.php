<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus opsi kategori 'Freeware' dari master kategori SMKI jika ada.
     */
    public function up(): void
    {
        if (Schema::hasTable('smki_kategoris')) {
            // Null-kan foreign key jika ada software yang sempat memakai kategori Freeware
            $freeware = DB::table('smki_kategoris')->where('nama_kategori', 'Freeware')->first();
            if ($freeware && Schema::hasTable('smki_software_standars')) {
                DB::table('smki_software_standars')
                    ->where('kategori_id', $freeware->id)
                    ->update(['kategori_id' => null]);
            }

            DB::table('smki_kategoris')->where('nama_kategori', 'Freeware')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

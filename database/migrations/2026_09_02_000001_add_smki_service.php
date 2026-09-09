<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sisipkan / pastikan master service SMKI terdaftar
        DB::table('services')->updateOrInsert(
            ['code' => 'SMKI'],
            [
                'id'          => 16,
                'parent_id'   => null,
                'code'        => 'SMKI',
                'name'        => 'SMKI',
                'description' => 'Surat Manajemen Keamanan Informasi',
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );

        // 2. Petakan izin layanan SMKI aktif (is_enabled = true) untuk seluruh 7 bidang
        if (Schema::hasTable('bidang_services') && Schema::hasTable('bidangs')) {
            $service = DB::table('services')->where('code', 'SMKI')->first();
            $bidangIds = DB::table('bidangs')->pluck('id')->toArray();

            if (empty($bidangIds)) {
                $bidangIds = [1, 2, 3, 4, 5, 6, 7];
            }

            if ($service) {
                foreach ($bidangIds as $bId) {
                    DB::table('bidang_services')->updateOrInsert(
                        [
                            'bidang_id'   => $bId,
                            'service_id'  => $service->id,
                        ],
                        [
                            'is_enabled'  => true,
                            'updated_at'  => now(),
                            'created_at'  => now(),
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        $service = DB::table('services')->where('code', 'SMKI')->first();
        if ($service) {
            DB::table('bidang_services')->where('service_id', $service->id)->delete();
            DB::table('services')->where('id', $service->id)->delete();
        }
    }
};

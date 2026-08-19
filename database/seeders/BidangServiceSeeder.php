<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Default Configuration Matrix:
        // APTIKA (id:3) -> All 4 Services Enabled
        // Bidang Lain (id: 1, 2, 4, 5, 6, 7) -> Services 1, 3, 4 Enabled; Service 2 (IKI_REPORT) Disabled

        $bidangIds = [1, 2, 3, 4, 5, 6, 7];
        $serviceIds = [1, 2, 3, 4];

        foreach ($bidangIds as $bId) {
            foreach ($serviceIds as $sId) {
                // Service 2 (IKI_REPORT) hanya aktif untuk APTIKA (bId = 3) secara default
                $isEnabled = ($sId === 2) ? ($bId === 3) : true;

                DB::table('bidang_services')->updateOrInsert(
                    ['bidang_id' => $bId, 'service_id' => $sId],
                    [
                        'is_enabled' => $isEnabled,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}

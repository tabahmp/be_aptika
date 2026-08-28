<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangServiceSeeder extends Seeder
{
    public function run(): void
    {
        // 7 Bidang (1 s/d 7)
        $bidangIds = [1, 2, 3, 4, 5, 6, 7];

        // 15 Services & Sub-Services (1 s/d 15)
        $serviceIds = range(1, 15);

        // Service IDs for IKI Report (2, 10, 11, 12, 13, 14, 15)
        $ikiServiceIds = [2, 10, 11, 12, 13, 14, 15];

        foreach ($bidangIds as $bId) {
            foreach ($serviceIds as $sId) {
                // IKI Report services & sub-services are enabled only for APTIKA (id: 3) by default
                $isEnabled = in_array($sId, $ikiServiceIds) ? ($bId === 3) : true;

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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\BidangService;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;

class BidangServiceController extends Controller
{
    use LogsAdminActivity;

    /**
     * GET /api/admin/bidang-services
     * Mengembalikan matriks lengkap service permission untuk seluruh bidang.
     */
    public function index()
    {
        $bidangs = Bidang::with(['services' => function ($query) {
            $query->select('services.id', 'services.parent_id', 'services.code', 'services.name')
                  ->withPivot('is_enabled');
        }])->get();

        $result = $bidangs->map(function ($bidang) {
            return [
                'bidang_id'   => $bidang->id,
                'bidang_code' => $bidang->code,
                'bidang_name' => $bidang->name,
                'services'    => $bidang->services->map(function ($service) {
                    return [
                        'service_id' => $service->id,
                        'parent_id'  => $service->parent_id,
                        'code'       => $service->code,
                        'name'       => $service->name,
                        'is_enabled' => (bool) $service->pivot->is_enabled,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * PUT /api/admin/bidang-services
     * Toggle status is_enabled untuk pasangan bidang_id + service_id.
     * Mencatat perubahan ke audit_logs.
     */
    public function update(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'bidang_id'  => 'required|exists:bidangs,id',
            'service_id' => 'required|exists:services,id',
            'is_enabled' => 'required|boolean',
        ]);

        // Ambil nilai sebelumnya
        $existing = BidangService::where('bidang_id', $validated['bidang_id'])
            ->where('service_id', $validated['service_id'])
            ->first();

        $oldEnabled = $existing ? (bool) $existing->is_enabled : true;

        $record = BidangService::updateOrCreate(
            [
                'bidang_id'  => $validated['bidang_id'],
                'service_id' => $validated['service_id'],
            ],
            [
                'is_enabled' => $validated['is_enabled'],
            ]
        );

        $bidang     = Bidang::find($validated['bidang_id']);
        $service    = \App\Models\Service::find($validated['service_id']);
        $statusText = $validated['is_enabled'] ? 'Aktif ✓' : 'Nonaktif ✗';
        $oldText    = $oldEnabled ? 'Aktif' : 'Nonaktif';

        $this->logActivity(
            'toggle',
            "Admin [{$admin->name}] mengubah status layanan [{$service->code}] untuk Bidang [{$bidang->name}]: {$oldText} → {$statusText}",
            null,
            ['bidang_id' => $validated['bidang_id'], 'service_id' => $validated['service_id'], 'is_enabled' => $oldEnabled],
            ['bidang_id' => $validated['bidang_id'], 'service_id' => $validated['service_id'], 'is_enabled' => (bool) $validated['is_enabled']]
        );

        return response()->json([
            'success' => true,
            'message' => "Status layanan {$service->code} untuk Bidang {$bidang->code} berhasil diubah menjadi {$statusText}.",
            'data'    => [
                'bidang_id'    => $record->bidang_id,
                'service_id'   => $record->service_id,
                'service_code' => $service->code,
                'is_enabled'   => (bool) $record->is_enabled,
            ],
        ]);
    }
}

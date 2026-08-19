<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\BidangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BidangServiceController extends Controller
{
    /**
     * GET /api/admin/bidang-services
     * Mengembalikan matriks lengkap service permission untuk seluruh 7 bidang.
     */
    public function index()
    {
        $bidangs = Bidang::with(['services' => function ($query) {
            $query->select('services.id', 'services.parent_id', 'services.code', 'services.name')
                  ->withPivot('is_enabled');
        }])->get();

        $result = $bidangs->map(function ($bidang) {
            return [
                'bidang_id' => $bidang->id,
                'bidang_code' => $bidang->code,
                'bidang_name' => $bidang->name,
                'services' => $bidang->services->map(function ($service) {
                    return [
                        'service_id' => $service->id,
                        'parent_id' => $service->parent_id,
                        'code' => $service->code,
                        'name' => $service->name,
                        'is_enabled' => (bool) $service->pivot->is_enabled,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * PUT /api/admin/bidang-services
     * Toggle status is_enabled untuk pasangan bidang_id + service_id.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bidang_id' => 'required|exists:bidangs,id',
            'service_id' => 'required|exists:services,id',
            'is_enabled' => 'required|boolean',
        ]);

        $record = BidangService::updateOrCreate(
            [
                'bidang_id' => $validated['bidang_id'],
                'service_id' => $validated['service_id'],
            ],
            [
                'is_enabled' => $validated['is_enabled'],
            ]
        );

        $bidang = Bidang::find($validated['bidang_id']);
        $service = \App\Models\Service::find($validated['service_id']);

        $statusText = $validated['is_enabled'] ? 'Aktif' : 'Nonaktif';

        return response()->json([
            'success' => true,
            'message' => "Status layanan {$service->code} untuk Bidang {$bidang->code} berhasil diubah menjadi {$statusText}.",
            'data' => [
                'bidang_id' => $record->bidang_id,
                'service_id' => $record->service_id,
                'service_code' => $service->code,
                'is_enabled' => (bool) $record->is_enabled,
            ],
        ]);
    }
}

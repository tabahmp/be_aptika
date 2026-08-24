<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * GET /api/admin/audit-logs
     * Daftar audit log dengan filter & paginasi (Super Admin Only).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AuditLog::with('causer:id,name,email')
                ->orderByDesc('created_at');

            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('causer_id')) {
                $query->where('causer_id', $request->causer_id);
            }

            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
            }

            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }

            if ($request->filled('search')) {
                $query->where('description', 'like', '%' . $request->search . '%');
            }

            $logs = $query->paginate($request->get('per_page', 25));

            return response()->json($logs);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AuditLogController error: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat audit log.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/admin/audit-logs/{id}
     * Detail satu entri audit log termasuk old/new values.
     */
    public function show(int $id): JsonResponse
    {
        $log = AuditLog::with('causer:id,name,email')->findOrFail($id);
        return response()->json($log);
    }
}

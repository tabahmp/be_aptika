<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureServiceEnabled
 *
 * Memastikan layanan (service) yang diminta berada dalam status aktif
 * (is_enabled == true) untuk bidang dari user yang terautentikasi.
 *
 * Penggunaan di route: middleware('service.enabled:ADMINISTRASI_SURAT')
 */
class EnsureServiceEnabled
{
    public function handle(Request $request, Closure $next, string $serviceCode): Response
    {
        $user = $request->user();

        if (!$user || !$user->bidang_id) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terikat pada bidang manapun.',
                'error_code' => 'BIDANG_REQUIRED',
            ], Response::HTTP_FORBIDDEN);
        }

        // Admin Aptika bypass pengecekan service (akses penuh)
        if ($user->isAdminAptika()) {
            return $next($request);
        }

        // Periksa status service dan parent service pada tabel bidang_services
        $serviceInfo = DB::table('services')
            ->leftJoin('bidang_services as bs_child', function ($join) use ($user) {
                $join->on('bs_child.service_id', '=', 'services.id')
                     ->where('bs_child.bidang_id', '=', $user->bidang_id);
            })
            ->leftJoin('services as parent', 'parent.id', '=', 'services.parent_id')
            ->leftJoin('bidang_services as bs_parent', function ($join) use ($user) {
                $join->on('bs_parent.service_id', '=', 'parent.id')
                     ->where('bs_parent.bidang_id', '=', $user->bidang_id);
            })
            ->where('services.code', $serviceCode)
            ->select(
                'services.name as service_name',
                'bs_child.is_enabled as child_enabled',
                'parent.name as parent_name',
                'bs_parent.is_enabled as parent_enabled'
            )
            ->first();

        if (!$serviceInfo) {
            return $next($request);
        }

        $childEnabled = (bool) $serviceInfo->child_enabled;
        $parentEnabled = $serviceInfo->parent_enabled !== null ? (bool) $serviceInfo->parent_enabled : true;

        if (!$childEnabled || !$parentEnabled) {
            return response()->json([
                'success' => false,
                'message' => "Layanan {$serviceInfo->service_name} saat ini dinonaktifkan untuk bidang Anda. Hubungi Administrator Aptika untuk mengaktifkannya.",
                'error_code' => 'SERVICE_DISABLED',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

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
 * untuk bidang dari user yang terautentikasi.
 *
 * Jika belum ada record bidang_services untuk service tersebut,
 * maka service dianggap aktif secara default.
 *
 * Penggunaan di route:
 * middleware('service.enabled:ADMINISTRASI_SURAT')
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

        // Admin Aptika memiliki akses penuh ke seluruh service.
        if ($user->isAdminAptika()) {
            return $next($request);
        }

        // Cari service berdasarkan code beserta status service
        // dan parent service untuk bidang user.
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

        // Jika service tidak ditemukan, jangan memblokir route.
        return $this->checkServiceAccess(
            $request,
            $next,
            $serviceInfo
        );
    }

    /**
     * Mengecek status service dan parent service.
     *
     * NULL berarti belum ada konfigurasi pada bidang tersebut,
     * sehingga dianggap aktif secara default.
     */
    private function checkServiceAccess(
        Request $request,
        Closure $next,
        $serviceInfo
    ): Response {
        if (!$serviceInfo) {
            return $next($request);
        }

        // NULL = default aktif.
        $childEnabled = $serviceInfo->child_enabled !== null
            ? (bool) $serviceInfo->child_enabled
            : true;

        // Parent juga menggunakan aturan NULL = aktif.
        $parentEnabled = $serviceInfo->parent_enabled !== null
            ? (bool) $serviceInfo->parent_enabled
            : true;

        // Service hanya aktif jika dirinya sendiri dan parent-nya aktif.
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

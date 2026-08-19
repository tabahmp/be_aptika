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

        // Periksa status service pada tabel bidang_services
        $isEnabled = DB::table('bidang_services')
            ->join('services', 'services.id', '=', 'bidang_services.service_id')
            ->where('bidang_services.bidang_id', $user->bidang_id)
            ->where('services.code', $serviceCode)
            ->value('bidang_services.is_enabled');

        if (!$isEnabled) {
            return response()->json([
                'success' => false,
                'message' => "Layanan {$serviceCode} saat ini dinonaktifkan untuk bidang Anda. Hubungi Administrator Aptika untuk mengaktifkannya.",
                'error_code' => 'SERVICE_DISABLED',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

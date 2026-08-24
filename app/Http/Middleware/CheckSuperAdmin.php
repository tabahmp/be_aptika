<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckSuperAdmin
 *
 * Memproteksi endpoint yang hanya boleh diakses oleh Super Admin,
 * yaitu pengguna dengan role 'admin' DAN berasal dari bidang APTIKA.
 * Admin dari bidang lain (Admin Bidang) akan ditolak.
 */
class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdminAptika()) {
            return response()->json([
                'message' => 'Akses ditolak. Fitur ini hanya tersedia untuk Super Admin.',
            ], 403);
        }

        return $next($request);
    }
}

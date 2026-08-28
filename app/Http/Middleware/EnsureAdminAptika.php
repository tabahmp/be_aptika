<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureAdminAptika
 *
 * Memastikan hanya pengguna dengan role 'admin' DAN berasal dari
 * Bidang APTIKA yang dapat mengakses Admin Panel API.
 */
class EnsureAdminAptika
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdminAptika()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Fitur Admin Panel hanya diperuntukkan bagi Administrator Bidang Aptika.',
                'error_code' => 'ADMIN_APTIKA_REQUIRED',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

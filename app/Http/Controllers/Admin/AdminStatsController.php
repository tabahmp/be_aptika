<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\User;
use App\Models\BidangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    /**
     * GET /api/admin/stats
     * Statistik ringkasan sistem untuk Dashboard Admin.
     * Dapat diakses oleh semua admin (Admin Bidang & Super Admin).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $admin         = $request->user();
            $isSuperAdmin  = $admin ? $admin->isAdminAptika() : false;

            // Super Admin melihat semua bidang, Admin Bidang hanya bidangnya sendiri
            $userQuery = User::query();
            if (!$isSuperAdmin && $admin) {
                $userQuery->where('bidang_id', $admin->bidang_id);
            }

            $totalUsers      = (clone $userQuery)->count();
            $activeUsers     = (clone $userQuery)->where('is_active', 1)->count();
            $inactiveUsers   = (clone $userQuery)->where('is_active', 0)->count();
            $adminCount      = (clone $userQuery)->where('role', 'admin')->count();

            // Statistik per bidang (hanya untuk super admin)
            $perBidang = null;
            if ($isSuperAdmin) {
                $perBidang = Bidang::withCount([
                    'users',
                    'users as active_users_count'   => fn ($q) => $q->where('is_active', 1),
                    'users as inactive_users_count'  => fn ($q) => $q->where('is_active', 0),
                    'users as admin_count'           => fn ($q) => $q->where('role', 'admin'),
                ])->get()->map(fn ($b) => [
                    'bidang_id'      => $b->id,
                    'bidang_name'    => $b->name,
                    'bidang_code'    => $b->code,
                    'total'          => $b->users_count,
                    'active'         => $b->active_users_count,
                    'inactive'       => $b->inactive_users_count,
                    'admins'         => $b->admin_count,
                ]);
            }

            // Statistik layanan aktif (hanya super admin melihat global)
            $activeServices = 0;
            $totalServices  = 0;
            if ($isSuperAdmin) {
                $totalServices  = BidangService::count();
                $activeServices = BidangService::where('is_enabled', 1)->count();
            }

            return response()->json([
                'total_users'     => $totalUsers,
                'active_users'    => $activeUsers,
                'inactive_users'  => $inactiveUsers,
                'admin_count'     => $adminCount,
                'per_bidang'      => $perBidang,
                'active_services' => $activeServices,
                'total_services'  => $totalServices,
                'is_super_admin'  => $isSuperAdmin,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AdminStatsController error: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat statistik admin.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

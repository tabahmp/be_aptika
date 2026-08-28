<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ActiveSessionController extends Controller
{
    use LogsAdminActivity;

    /**
     * GET /api/admin/active-sessions
     * Daftar semua token Sanctum aktif (belum expired), tidak termasuk token impersonasi.
     */
    public function index(): JsonResponse
    {
        $sessions = PersonalAccessToken::with('tokenable:id,name,email,bidang_id')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->where('name', '!=', 'impersonate')
            ->orderByDesc('last_used_at')
            ->get()
            ->map(fn ($t) => [
                'token_id'     => $t->id,
                'user_id'      => $t->tokenable_id,
                'user_name'    => $t->tokenable?->name,
                'user_email'   => $t->tokenable?->email,
                'token_name'   => $t->name,
                'last_used_at' => $t->last_used_at,
                'created_at'   => $t->created_at,
                'expires_at'   => $t->expires_at,
            ]);

        return response()->json(['data' => $sessions]);
    }

    /**
     * DELETE /api/admin/active-sessions/{tokenId}
     * Force Logout: mencabut token Sanctum milik pengguna lain secara paksa.
     */
    public function forceLogout(Request $request, int $tokenId): JsonResponse
    {
        $token = PersonalAccessToken::findOrFail($tokenId);
        $admin = $request->user();

        // Cegah admin me-force-logout diri sendiri
        if ($token->tokenable_id === $admin->id) {
            return response()->json([
                'message' => 'Tidak dapat memaksa logout sesi Anda sendiri.',
            ], 422);
        }

        $targetUser = User::find($token->tokenable_id);

        $this->logActivity(
            'force_logout',
            "Super Admin [{$admin->name}] memaksa logout sesi token #{$tokenId} milik [{$targetUser?->name}] ({$targetUser?->email})",
            $targetUser,
        );

        $token->delete();

        return response()->json([
            'message' => "Sesi pengguna {$targetUser?->name} telah diakhiri paksa.",
        ]);
    }
}

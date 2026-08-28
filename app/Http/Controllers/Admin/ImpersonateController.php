<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImpersonateController extends Controller
{
    use LogsAdminActivity;

    /**
     * POST /api/admin/impersonate/{userId}
     * Super Admin membuat token sementara atas nama user target (berlaku 2 jam).
     * Frontend menyimpan token ini lalu menukar ke token admin asli saat selesai.
     */
    public function impersonate(Request $request, int $userId): JsonResponse
    {
        $admin  = $request->user();
        $target = User::with('bidang')->findOrFail($userId);

        if ($admin->id === $target->id) {
            return response()->json([
                'message' => 'Tidak dapat mengimpersonasi diri sendiri.',
            ], 422);
        }

        // Hapus token impersonasi sebelumnya agar tidak menumpuk
        $target->tokens()->where('name', 'impersonate')->delete();

        // Buat token sementara berlaku 2 jam
        $token = $target->createToken(
            'impersonate',
            ['*'],
            now()->addHours(2)
        )->plainTextToken;

        $this->logActivity(
            'impersonate',
            "Super Admin [{$admin->name}] memulai sesi impersonasi sebagai [{$target->name}] dari bidang [{$target->bidang?->name}]",
            $target,
            null,
            ['impersonated_as' => $target->email, 'bidang' => $target->bidang?->name]
        );

        return response()->json([
            'message'           => "Sesi impersonasi dimulai sebagai {$target->name}.",
            'impersonate_token' => $token,
            'target_user'       => [
                'id'    => $target->id,
                'name'  => $target->name,
                'email' => $target->email,
                'role'  => $target->role,
                'bidang'=> $target->bidang?->name,
            ],
        ]);
    }

    /**
     * DELETE /api/admin/impersonate/{userId}
     * Mengakhiri sesi impersonasi — cabut token impersonasi milik target.
     */
    public function stopImpersonate(Request $request, int $userId): JsonResponse
    {
        $admin  = $request->user();
        $target = User::findOrFail($userId);

        $deleted = $target->tokens()->where('name', 'impersonate')->delete();

        if ($deleted) {
            $this->logActivity(
                'impersonate',
                "Super Admin [{$admin->name}] mengakhiri sesi impersonasi dari [{$target->name}]",
                $target,
            );
        }

        return response()->json([
            'message' => 'Sesi impersonasi telah diakhiri.',
        ]);
    }
}

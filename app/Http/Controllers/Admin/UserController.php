<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use LogsAdminActivity;

    /**
     * Display a listing of users.
     * Super Admin (Aptika) → semua bidang.
     * Admin Bidang → hanya bidangnya sendiri.
     */
    public function index(Request $request): JsonResponse
    {
        $admin        = $request->user();
        $isSuperAdmin = $admin->isAdminAptika();

        $query = User::with('bidang')->orderBy('name');

        // Admin biasa hanya bisa melihat user di bidangnya sendiri
        if (!$isSuperAdmin) {
            $query->where('bidang_id', $admin->bidang_id);
        }

        // Filter opsional
        if ($request->filled('bidang_id')) {
            $query->where('bidang_id', $request->bidang_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn ($q) =>
                $q->where('name', 'like', $s)
                  ->orWhere('email', 'like', $s)
                  ->orWhere('position', 'like', $s)
            );
        }

        $users = $query->get();
        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8',
            'role'      => ['required', 'string', Rule::in(['admin', 'user'])],
            'bidang_id' => 'required|exists:bidangs,id',
            'is_active' => 'required|integer|in:0,1',
            'position'  => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'jabatan'   => 'nullable|string|max:255',
            'no_telp'   => 'nullable|string|max:50',
        ]);

        if (!empty($validated['jabatan']) && empty($validated['position'])) {
            $validated['position'] = $validated['jabatan'];
        }
        if (!empty($validated['no_telp']) && empty($validated['phone'])) {
            $validated['phone'] = $validated['no_telp'];
        }
        unset($validated['jabatan'], $validated['no_telp']);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->load('bidang');

        $this->logActivity(
            'create',
            "Admin [{$admin->name}] membuat akun pengguna baru: [{$user->name}] ({$user->email}) pada bidang [{$user->bidang?->name}] dengan role [{$user->role}]",
            $user,
            null,
            $user->only(['name', 'email', 'role', 'bidang_id', 'is_active', 'position'])
        );

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user,
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id): JsonResponse
    {
        $user = User::with('bidang')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        $user  = User::with('bidang')->findOrFail($id);

        $oldValues = $user->only(['name', 'email', 'role', 'bidang_id', 'is_active', 'position', 'phone']);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:8',
            'role'      => ['required', 'string', Rule::in(['admin', 'user'])],
            'bidang_id' => 'nullable|exists:bidangs,id',
            'is_active' => 'required|integer|in:0,1',
            'position'  => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'jabatan'   => 'nullable|string|max:255',
            'no_telp'   => 'nullable|string|max:50',
        ]);

        if (!empty($validated['jabatan']) && empty($validated['position'])) {
            $validated['position'] = $validated['jabatan'];
        }
        if (!empty($validated['no_telp']) && empty($validated['phone'])) {
            $validated['phone'] = $validated['no_telp'];
        }
        unset($validated['jabatan'], $validated['no_telp']);

        $passwordReset = false;
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
            $passwordReset = true;
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load('bidang');

        $actionLabel = $passwordReset ? 'reset_password' : 'update';
        $desc = $passwordReset
            ? "Admin [{$admin->name}] mereset password akun [{$user->name}] ({$user->email})"
            : "Admin [{$admin->name}] memperbarui data pengguna [{$user->name}] ({$user->email})";

        $this->logActivity(
            $actionLabel,
            $desc,
            $user,
            $oldValues,
            $user->fresh()->only(['name', 'email', 'role', 'bidang_id', 'is_active', 'position', 'phone'])
        );

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user,
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        $user  = User::with('bidang')->findOrFail($id);

        if ($user->id === $admin->id) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ], 400);
        }

        $snapshot = $user->only(['name', 'email', 'role', 'bidang_id']);
        $bidangName = $user->bidang?->name ?? '-';

        if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }

        // Cabut semua token user sebelum dihapus
        $user->tokens()->delete();
        $user->delete();

        $this->logActivity(
            'delete',
            "Admin [{$admin->name}] menghapus akun: [{$snapshot['name']}] ({$snapshot['email']}) dari bidang [{$bidangName}]",
            null,
            $snapshot
        );

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}

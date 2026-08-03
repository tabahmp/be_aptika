<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        
        // Handle alias parameters if provided
        if (array_key_exists('jabatan', $validated) && (!isset($validated['position']) || $validated['position'] === null)) {
            $validated['position'] = $validated['jabatan'];
        }
        if (array_key_exists('no_telp', $validated) && (!isset($validated['phone']) || $validated['phone'] === null)) {
            $validated['phone'] = $validated['no_telp'];
        }

        if (!empty($validated['password'])) {
            if (empty($validated['current_password'])) {
                return response()->json([
                    'message' => 'Password lama wajib diisi untuk mengganti password.',
                    'errors' => ['current_password' => ['Password lama wajib diisi.']]
                ], 422);
            }
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        unset($validated['current_password']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle remove_avatar request
        if (!empty($validated['remove_avatar']) || $request->input('remove_avatar') === 'true' || $request->input('remove_avatar') === '1') {
            if ($user->avatar && !str_starts_with($user->avatar, 'data:image/') && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }
        // Handle new avatar string (Base64 data URI) - decode and save as stored file (~30 chars) to prevent VARCHAR(255) truncation errors
        else if ($request->filled('avatar') && is_string($request->input('avatar')) && str_starts_with($request->input('avatar'), 'data:image/')) {
            try {
                $base64Str = $request->input('avatar');
                @list($typeInfo, $data) = explode(';', $base64Str);
                @list(, $data) = explode(',', $data);
                $imageBytes = base64_decode($data);

                $ext = 'jpg';
                if (str_contains($typeInfo, 'png')) $ext = 'png';
                else if (str_contains($typeInfo, 'webp')) $ext = 'webp';
                else if (str_contains($typeInfo, 'gif')) $ext = 'gif';

                $filename = 'avatars/avatar_' . time() . '_' . uniqid() . '.' . $ext;
                Storage::disk('public')->put($filename, $imageBytes);

                if ($user->avatar && !str_starts_with($user->avatar, 'data:image/') && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $user->avatar = $filename;
            } catch (\Throwable $e) {
                if (strlen($request->input('avatar')) <= 255) {
                    $user->avatar = $request->input('avatar');
                }
            }
        }
        // Handle new avatar file upload
        else if ($request->hasFile('avatar')) {
            if ($user->avatar && !str_starts_with($user->avatar, 'data:image/') && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->currentAccessToken()) {
            $user->tokens()->delete();
        }

        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}

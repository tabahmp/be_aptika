<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait LogsAdminActivity
{
    /**
     * Catat aksi admin ke tabel audit_logs.
     *
     * @param string     $action      Kode aksi: create|update|delete|toggle|reset_password|impersonate|force_logout
     * @param string     $description Keterangan tindakan human-readable (dalam Bahasa Indonesia)
     * @param Model|null $subject     Model yang menjadi target aksi (opsional)
     * @param array|null $oldValues   Snapshot nilai sebelum perubahan (opsional)
     * @param array|null $newValues   Snapshot nilai sesudah perubahan (opsional)
     */
    protected function logActivity(
        string  $action,
        string  $description,
        ?Model  $subject    = null,
        ?array  $oldValues  = null,
        ?array  $newValues  = null
    ): void {
        try {
            /** @var Request $request */
            $request = app(Request::class);

            $causerId = auth('sanctum')->id()
                ?? auth()->id()
                ?? $request->user()?->id;

            if (!$causerId) {
                return;
            }

            AuditLog::create([
                'causer_id'    => $causerId,
                'causer_type'  => 'App\\Models\\User',
                'action'       => $action,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'description'  => $description,
                'old_values'   => $oldValues,
                'new_values'   => $newValues,
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string)$request->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Audit log failed: " . $e->getMessage());
        }
    }
}

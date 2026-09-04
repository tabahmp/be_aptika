<?php

namespace App\Http\Controllers\TaskManagement;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardMember;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskAttachment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    /**
     * Menampilkan daftar attachment berdasarkan task_id.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => 'User belum login.',
            ], 401);
        }

        $request->validate([
            'task_id' => 'required|exists:tasks,id',
        ]);

        try {
            $taskId = $request->query('task_id');
            $task = Task::findOrFail($taskId);
            $userId = Auth::id();
            $isAdmin = Auth::user()->role === 'admin';

            $isMember = BoardMember::where('board_id', $task->board_id)
                ->where('user_id', $userId)
                ->where('membership_status', 'accepted')
                ->exists();

            $isPm = (int) $task->board->created_by === $userId;

            if (!$isAdmin && !$isPm && !$isMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke lampiran task ini.',
                    'errors' => 'Forbidden access.',
                ], 403);
            }

            $attachments = TaskAttachment::with(['uploader:id,name'])
                ->where('task_id', $taskId)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($att) {
                    return [
                        'id' => $att->id,
                        'task_id' => $att->task_id,
                        'file_name' => $att->file_name,
                        'file_path' => $att->file_path,
                        'file_url' => asset('storage/' . $att->file_path),
                        'uploaded_by' => $att->uploaded_by,
                        'uploader_name' => $att->uploader?->name ?? 'User',
                        'created_at' => $att->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Daftar lampiran berhasil diambil.',
                'data' => $attachments,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lampiran.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengunggah attachment baru untuk task.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => 'User belum login.',
            ], 401);
        }

        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png,csv,txt',
        ]);

        try {
            $task = Task::findOrFail($request->input('task_id'));
            $userId = Auth::id();
            $isAdmin = Auth::user()->role === 'admin';
            $isPm = (int) $task->board->created_by === $userId;
            $isAssignee = (int) $task->assigned_to === $userId;

            $member = BoardMember::where('board_id', $task->board_id)
                ->where('user_id', $userId)
                ->where('membership_status', 'accepted')
                ->first();

            $isActivatedMember = $member && (bool) $member->can_create_task;

            if (!$isAdmin && !$isPm && !$isAssignee && !$isActivatedMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berwenang mengunggah lampiran pada task ini.',
                    'errors' => 'Hanya Admin, PM, Assignee, atau anggota yang diaktifkan yang dapat mengunggah berkas.',
                ], 403);
            }

            if ($task->status === 'done') {
                return response()->json([
                    'success' => false,
                    'message' => 'Task yang sudah selesai (Done) tidak dapat dimodifikasi.',
                ], 403);
            }

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $path = $file->store('task_attachments', 'public');

            $attachment = DB::transaction(function () use ($task, $userId, $originalName, $path) {
                $att = TaskAttachment::create([
                    'task_id' => $task->id,
                    'uploaded_by' => $userId,
                    'file_name' => $originalName,
                    'file_path' => $path,
                ]);

                TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'activity' => 'User mengunggah lampiran: ' . $originalName,
                ]);

                return $att;
            });

            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil diunggah.',
                'data' => [
                    'id' => $attachment->id,
                    'task_id' => $attachment->task_id,
                    'file_name' => $attachment->file_name,
                    'file_path' => $attachment->file_path,
                    'file_url' => asset('storage/' . $attachment->file_path),
                    'uploaded_by' => $attachment->uploaded_by,
                    'uploader_name' => Auth::user()->name,
                    'created_at' => $attachment->created_at,
                ],
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah lampiran.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus attachment.
     */
    public function destroy(string $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => 'User belum login.',
            ], 401);
        }

        try {
            $attachment = TaskAttachment::with(['task.board'])->findOrFail($id);
            $task = $attachment->task;
            $userId = Auth::id();
            $isAdmin = Auth::user()->role === 'admin';
            $isPm = (int) $task->board->created_by === $userId;
            $isUploader = (int) $attachment->uploaded_by === $userId;

            $member = BoardMember::where('board_id', $task->board_id)
                ->where('user_id', $userId)
                ->where('membership_status', 'accepted')
                ->first();

            $isActivatedMember = $member && (bool) $member->can_create_task;

            if (!$isAdmin && !$isPm && !$isUploader && !$isActivatedMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berwenang menghapus lampiran ini.',
                    'errors' => 'Forbidden access.',
                ], 403);
            }

            if ($task->status === 'done') {
                return response()->json([
                    'success' => false,
                    'message' => 'Task yang sudah selesai (Done) tidak dapat dimodifikasi.',
                ], 403);
            }

            DB::transaction(function () use ($attachment, $task, $userId) {
                if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }

                $fileName = $attachment->file_name;
                $attachment->delete();

                TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'activity' => 'User menghapus lampiran: ' . $fileName,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil dihapus.',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lampiran tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus lampiran.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectDataSeeder extends Seeder
{
    /**
     * Seed data project, task, activity, dan notification.
     */
    public function run(): void
    {
        /*
         * ============================================================
         * BOARDS
         * ============================================================
         */

        $boards = [
            [
                'id' => 1,
                'name' => 'Test',
                'description' => 'test ini untuk tes aja',
                'created_by' => 1,
                'start_date' => null,
                'end_date' => null,
                'status' => 'completed',
                'visibility' => 'public',
                'created_at' => '2026-07-28 23:25:45',
                'updated_at' => '2026-07-28 23:25:45',
            ],
            [
                'id' => 3,
                'name' => 'Proyek Manajemen Tugas',
                'description' => 'Untuk tes saja',
                'created_by' => 1,
                'start_date' => null,
                'end_date' => null,
                'status' => 'active',
                'visibility' => 'public',
                'created_at' => '2026-08-03 07:36:36',
                'updated_at' => '2026-08-03 07:36:36',
            ],
            [
                'id' => 7,
                'name' => 'untuk tes aja',
                'description' => 'untuk tes qa aja',
                'created_by' => 1,
                'start_date' => null,
                'end_date' => null,
                'status' => 'active',
                'visibility' => 'public',
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
        ];

        foreach ($boards as $board) {
            DB::table('boards')->updateOrInsert(
                ['id' => $board['id']],
                $board
            );
        }

        /*
         * ============================================================
         * BOARD MEMBERS
         * ============================================================
         */

        $boardMembers = [
            [
                'id' => 1,
                'board_id' => 1,
                'user_id' => 1,
                'role' => 'pm',
                'membership_status' => 'accepted',
                'joined_at' => '2026-07-28 23:25:45',
                'created_at' => '2026-07-28 23:25:45',
                'updated_at' => '2026-07-28 23:25:45',
            ],
            [
                'id' => 3,
                'board_id' => 3,
                'user_id' => 1,
                'role' => 'pm',
                'membership_status' => 'accepted',
                'joined_at' => '2026-08-03 07:36:36',
                'created_at' => '2026-08-03 07:36:36',
                'updated_at' => '2026-08-03 07:36:36',
            ],
            [
                'id' => 17,
                'board_id' => 7,
                'user_id' => 1,
                'role' => 'pm',
                'membership_status' => 'accepted',
                'joined_at' => '2026-08-04 10:46:23',
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
        ];

        foreach ($boardMembers as $member) {
            DB::table('board_members')->updateOrInsert(
                ['id' => $member['id']],
                $member
            );
        }

        /*
         * ============================================================
         * TASKS
         * ============================================================
         */

        $tasks = [
            [
                'id' => 1,
                'board_id' => 7,
                'created_by' => 1,
                'assigned_to' => null,
                'title' => 'membuat backend',
                'description' => null,
                'priority' => 'medium',
                'status' => 'in_review',
                'start_date' => null,
                'due_date' => null,
                'completed_at' => null,
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
            [
                'id' => 2,
                'board_id' => 7,
                'created_by' => 1,
                'assigned_to' => null,
                'title' => 'tugas 1',
                'description' => null,
                'priority' => 'medium',
                'status' => 'in_review',
                'start_date' => null,
                'due_date' => null,
                'completed_at' => null,
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
            [
                'id' => 3,
                'board_id' => 7,
                'created_by' => 1,
                'assigned_to' => null,
                'title' => 'membuat frontend',
                'description' => null,
                'priority' => 'medium',
                'status' => 'in_progress',
                'start_date' => null,
                'due_date' => null,
                'completed_at' => null,
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
        ];

        foreach ($tasks as $task) {
            DB::table('tasks')->updateOrInsert(
                ['id' => $task['id']],
                $task
            );
        }

        /*
         * ============================================================
         * TASK ACTIVITIES
         * ============================================================
         */

        $activities = [
            [
                'id' => 1,
                'task_id' => 1,
                'user_id' => 1,
                'activity' => 'Task dibuat',
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
            [
                'id' => 2,
                'task_id' => 2,
                'user_id' => 1,
                'activity' => 'Task dibuat',
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
            [
                'id' => 3,
                'task_id' => 1,
                'user_id' => 1,
                'activity' => 'Status berubah',
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
            [
                'id' => 4,
                'task_id' => 3,
                'user_id' => 1,
                'activity' => 'Task dibuat',
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
            [
                'id' => 5,
                'task_id' => 3,
                'user_id' => 1,
                'activity' => 'Task diassign',
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
        ];

        foreach ($activities as $activity) {
            DB::table('task_activities')->updateOrInsert(
                ['id' => $activity['id']],
                $activity
            );
        }

        /*
         * ============================================================
         * NOTIFICATIONS
         * ============================================================
         */

        $notifications = [
            [
                'id' => 1,
                'user_id' => 1,
                'type' => 'JOIN_REQUEST',
                'title' => 'Permintaan bergabung board',
                'message' => 'Nasywa Nur mengirim permintaan untuk bergabung ke board Proyek Manajemen Tugas.',
                'is_read' => false,
                'read_at' => null,
                'board_id' => 3,
                'task_id' => null,
                'created_by_user_id' => null,
                'created_at' => '2026-08-03 07:36:36',
                'updated_at' => '2026-08-03 07:36:36',
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'type' => 'JOIN_REQUEST',
                'title' => 'Permintaan bergabung board',
                'message' => 'Ipul Syaifulloh mengirim permintaan untuk bergabung ke board Proyek Manajemen Tugas.',
                'is_read' => false,
                'read_at' => null,
                'board_id' => 3,
                'task_id' => null,
                'created_by_user_id' => null,
                'created_at' => '2026-08-03 07:36:36',
                'updated_at' => '2026-08-03 07:36:36',
            ],
            [
                'id' => 4,
                'user_id' => 1,
                'type' => 'JOIN_REQUEST',
                'title' => 'Permintaan bergabung board',
                'message' => 'Ipul Syaifulloh mengirim permintaan untuk bergabung ke board Test.',
                'is_read' => false,
                'read_at' => null,
                'board_id' => 1,
                'task_id' => null,
                'created_by_user_id' => null,
                'created_at' => '2026-07-28 23:25:45',
                'updated_at' => '2026-07-28 23:25:45',
            ],
            [
                'id' => 12,
                'user_id' => 1,
                'type' => 'JOIN_REQUEST',
                'title' => 'Permintaan bergabung board',
                'message' => 'Nasywa Nur mengirim permintaan untuk bergabung ke board untuk tes aja.',
                'is_read' => false,
                'read_at' => null,
                'board_id' => 7,
                'task_id' => null,
                'created_by_user_id' => null,
                'created_at' => '2026-08-04 10:46:23',
                'updated_at' => '2026-08-04 10:46:23',
            ],
        ];

        foreach ($notifications as $notification) {
            DB::table('notifications')->updateOrInsert(
                ['id' => $notification['id']],
                $notification
            );
        }
    }
}
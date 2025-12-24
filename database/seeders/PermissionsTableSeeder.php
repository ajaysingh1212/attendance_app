<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'id'    => 1,
                'title' => 'user_management_access',
            ],
            [
                'id'    => 2,
                'title' => 'permission_create',
            ],
            [
                'id'    => 3,
                'title' => 'permission_edit',
            ],
            [
                'id'    => 4,
                'title' => 'permission_show',
            ],
            [
                'id'    => 5,
                'title' => 'permission_delete',
            ],
            [
                'id'    => 6,
                'title' => 'permission_access',
            ],
            [
                'id'    => 7,
                'title' => 'role_create',
            ],
            [
                'id'    => 8,
                'title' => 'role_edit',
            ],
            [
                'id'    => 9,
                'title' => 'role_show',
            ],
            [
                'id'    => 10,
                'title' => 'role_delete',
            ],
            [
                'id'    => 11,
                'title' => 'role_access',
            ],
            [
                'id'    => 12,
                'title' => 'user_create',
            ],
            [
                'id'    => 13,
                'title' => 'user_edit',
            ],
            [
                'id'    => 14,
                'title' => 'user_show',
            ],
            [
                'id'    => 15,
                'title' => 'user_delete',
            ],
            [
                'id'    => 16,
                'title' => 'user_access',
            ],
            [
                'id'    => 17,
                'title' => 'audit_log_show',
            ],
            [
                'id'    => 18,
                'title' => 'audit_log_access',
            ],
            [
                'id'    => 19,
                'title' => 'master_data_access',
            ],
            [
                'id'    => 20,
                'title' => 'attendance_access',
            ],
            [
                'id'    => 21,
                'title' => 'company_create',
            ],
            [
                'id'    => 22,
                'title' => 'company_edit',
            ],
            [
                'id'    => 23,
                'title' => 'company_show',
            ],
            [
                'id'    => 24,
                'title' => 'company_delete',
            ],
            [
                'id'    => 25,
                'title' => 'company_access',
            ],
            [
                'id'    => 26,
                'title' => 'branch_create',
            ],
            [
                'id'    => 27,
                'title' => 'branch_edit',
            ],
            [
                'id'    => 28,
                'title' => 'branch_show',
            ],
            [
                'id'    => 29,
                'title' => 'branch_delete',
            ],
            [
                'id'    => 30,
                'title' => 'branch_access',
            ],
            [
                'id'    => 31,
                'title' => 'attendance_detail_create',
            ],
            [
                'id'    => 32,
                'title' => 'attendance_detail_edit',
            ],
            [
                'id'    => 33,
                'title' => 'attendance_detail_show',
            ],
            [
                'id'    => 34,
                'title' => 'attendance_detail_delete',
            ],
            [
                'id'    => 35,
                'title' => 'attendance_detail_access',
            ],
            [
                'id'    => 36,
                'title' => 'leave_request_create',
            ],
            [
                'id'    => 37,
                'title' => 'leave_request_edit',
            ],
            [
                'id'    => 38,
                'title' => 'leave_request_show',
            ],
            [
                'id'    => 39,
                'title' => 'leave_request_delete',
            ],
            [
                'id'    => 40,
                'title' => 'leave_request_access',
            ],
            [
                'id'    => 41,
                'title' => 'notification_create',
            ],
            [
                'id'    => 42,
                'title' => 'notification_edit',
            ],
            [
                'id'    => 43,
                'title' => 'notification_show',
            ],
            [
                'id'    => 44,
                'title' => 'notification_delete',
            ],
            [
                'id'    => 45,
                'title' => 'notification_access',
            ],
            [
                'id'    => 46,
                'title' => 'app_update_create',
            ],
            [
                'id'    => 47,
                'title' => 'app_update_edit',
            ],
            [
                'id'    => 48,
                'title' => 'app_update_show',
            ],
            [
                'id'    => 49,
                'title' => 'app_update_delete',
            ],
            [
                'id'    => 50,
                'title' => 'app_update_access',
            ],
            [
                'id'    => 51,
                'title' => 'profile_password_edit',
            ],
        ];

        Permission::insert($permissions);
    }
}

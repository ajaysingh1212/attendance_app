<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AuditLogPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = collect([
            'audit_log_access',
            'audit_log_show',
        ])->map(function ($title) {
            return Permission::firstOrCreate(['title' => $title]);
        });

        $admin = Role::find(1);

        if ($admin) {
            $admin->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        }
    }
}

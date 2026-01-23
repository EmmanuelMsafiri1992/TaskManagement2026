<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder can be run safely multiple times - it won't duplicate data.
     *
     * @return void
     */
    public function run()
    {
        $this->seedRoles();
        $this->seedPermissions();
        $this->assignPermissionsToAdmin();
    }

    protected function seedRoles()
    {
        $roles = ['Admin', 'User', 'Marketer', 'Developer'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $this->command->info('Roles seeded successfully!');
    }

    protected function seedPermissions()
    {
        $permissions = [
            // Project permissions
            'project:create',
            'project:update',
            'project:delete',
            // Task permissions
            'task:update',
            'task:delete',
            'task-list:create',
            'task-list:update',
            'task-list:delete',
            // Label permissions
            'label:view',
            'label:create',
            'label:update',
            'label:delete',
            // User permissions
            'user:view',
            'user:create',
            'user:update',
            'user:delete',
            // Role permissions
            'role:view',
            'role:create',
            'role:update',
            'role:delete',
            // Settings permissions
            'setting:general',
            'setting:email',
            'setting:updates',
            // Additional permissions
            'employee:view',
            'leave:view',
            'advance_request:view',
            'company:view',
            'payroll:view',
            'client:view',
            'quotation:view',
            'expense:view',
            'income:view',
            'service_provider:view',
            'audit:view',
            'inventory:view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $this->command->info('Permissions seeded successfully!');
    }

    protected function assignPermissionsToAdmin()
    {
        $adminRole = Role::where('name', 'Admin')->first();

        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
            $this->command->info('All permissions assigned to Admin role!');
        }
    }
}

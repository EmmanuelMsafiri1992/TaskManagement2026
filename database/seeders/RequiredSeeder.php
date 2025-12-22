<?php

namespace Database\Seeders;

use AhsanDev\Support\NestedCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RequiredSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Cache::flush();

        $this->users();
        $this->categories();
        $this->options();
        $this->roles();
        $this->permissions();
        $this->pivots();
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    protected function users()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
            ],
        ]);
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    protected function categories()
    {
        $categories = [
            'Task Priority' => [
                ['name' => 'Urgent', 'color' => '#b91c1c'],
                ['name' => 'High', 'color' => '#ea580c'],
                ['name' => 'Medium', 'color' => '#0ea5e9'],
                ['name' => 'Low', 'color' => '#78716c'],
            ],
        ];

        DB::table('categories')->insert(
            NestedCategory::make($categories)->get()
        );
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    protected function options()
    {
        $options = [
            'app_name' => 'Spack',
            'app_url' => 'http://spack.test',
            'app_locale' => 'en',
            'app_timezone' => 'UTC',
            'email_config' => false,
            'mail_driver' => 'smtp',
            'mail_encryption' => 'null',
            'app_direction' => 'ltr',
            'header_logo' => 'img/header_logo.png',
            'app_updates' => [
                'update_available' => false,
                'version' => '3.2.10',
                'new_version' => null,
                'checked_at' => null,
            ],
        ];

        DB::table('options')->insert(
            collect($options)->map(function ($value, $key) {
                return ['key' => $key, 'value' => is_array($value) ? json_encode($value) : $value];
            })->values()->all()
        );
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    protected function roles()
    {
        $data = [
            'Admin',
            'User',
            'Marketer',
            'Developer',
        ];

        $data = collect($data)->map(function ($value) {
            return ['name' => $value];
        })->all();

        DB::table('roles')->insert($data);
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    protected function permissions()
    {
        $data = [
            'project:create',
            'project:update',
            'project:delete',
            'task:update',
            'task:delete',
            'task-list:create',
            'task-list:update',
            'task-list:delete',
            'label:view',
            'label:create',
            'label:update',
            'label:delete',
            'user:view',
            'user:create',
            'user:update',
            'user:delete',
            'role:view',
            'role:create',
            'role:update',
            'role:delete',
            'setting:general',
            'setting:email',
            'setting:updates',
        ];

        $data = collect($data)->map(function ($value) {
            return ['name' => $value];
        })->all();

        DB::table('permissions')->insert($data);
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    protected function pivots()
    {
        // Assign permissions to the Admin role
        $adminRole = Role::where('name', 'Admin')->first();
        $permissions = Permission::get();
        $adminRole->permissions()->sync($permissions);

        // Assign Admin role to admin user
        $adminUser = User::where('email', 'admin@example.com')->first();
        $adminUser->assignRole($adminRole->name);

        // Assign Marketer role to regular user
        $marketerRole = Role::where('name', 'Marketer')->first();
        $regularUser = User::where('email', 'user@example.com')->first();
        $regularUser->assignRole($marketerRole->name);
    }
}

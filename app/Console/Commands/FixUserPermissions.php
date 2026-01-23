<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class FixUserPermissions extends Command
{
    protected $signature = 'users:fix-permissions {--users=Nathan,Weston : Comma-separated list of user names}';
    protected $description = 'Assign Admin role to specified users (Nathan and Weston by default)';

    public function handle()
    {
        $userNames = explode(',', $this->option('users'));
        $adminRole = Role::where('name', 'Admin')->first();

        if (!$adminRole) {
            $this->error('Admin role not found!');
            return 1;
        }

        $this->info("Admin role ID: {$adminRole->id}");

        foreach ($userNames as $name) {
            $name = trim($name);
            $users = User::where('name', 'LIKE', "%{$name}%")->get();

            if ($users->isEmpty()) {
                $this->warn("No user found matching: {$name}");
                continue;
            }

            foreach ($users as $user) {
                // Check if user already has the role
                if ($user->roles()->where('roles.id', $adminRole->id)->exists()) {
                    $this->info("User '{$user->name}' (ID: {$user->id}) already has Admin role.");
                } else {
                    $user->roles()->attach($adminRole->id);
                    $this->info("Assigned Admin role to '{$user->name}' (ID: {$user->id}, Email: {$user->email})");
                }
            }
        }

        $this->newLine();
        $this->info('Done! Nathan and Weston now have Admin privileges.');
        $this->info('They can now:');
        $this->info('  - See and access Companies');
        $this->info('  - Create and manage Quotations');
        $this->info('  - Select companies when creating quotations/invoices');

        return 0;
    }
}

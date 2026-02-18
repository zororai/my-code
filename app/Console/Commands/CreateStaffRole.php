<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateStaffRole extends Command
{
    protected $signature = 'fix:roles';
    protected $description = 'Create missing roles';

    public function handle()
    {
        $roles = ['Admin', 'Teacher', 'Parent', 'Student', 'Staff'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $this->info("Role '{$roleName}' ensured.");
        }
        
        return 0;
    }
}

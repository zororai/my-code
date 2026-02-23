<?php

use Illuminate\Database\Seeder;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if super admin already exists
        $superAdmin = User::where('email', 'superadmin@roshs.ac.zw')->first();
        
        if (!$superAdmin) {
            // Create super admin user
            $superAdmin = User::create([
                'name' => 'Super Administrator',
                'email' => 'superadmin@roshs.ac.zw',
                'password' => Hash::make('SuperAdmin@2026!'),
                'is_active' => true,
                'is_super_admin' => true, // Special flag to identify this user
            ]);

            // Assign Admin role
            $adminRole = Role::firstOrCreate(['name' => 'Admin']);
            $superAdmin->assignRole($adminRole);

            // Give all permissions
            $allPermissions = Permission::all();
            $superAdmin->givePermissionTo($allPermissions);

            $this->command->info('Super Admin created successfully!');
            $this->command->info('Email: superadmin@roshs.ac.zw');
            $this->command->info('Password: SuperAdmin@2026!');
        } else {
            $this->command->info('Super Admin already exists.');
        }
    }
}

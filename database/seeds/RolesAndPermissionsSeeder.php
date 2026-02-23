<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        // Permission::create(['name' => 'edit articles']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role = Role::firstOrCreate(['name' => 'Teacher']);
        $role = Role::firstOrCreate(['name' => 'Parent']);
        $role = Role::firstOrCreate(['name' => 'Student']);
        $role = Role::firstOrCreate(['name' => 'Staff']);

        // $role->givePermissionTo('edit articles');
    }
}

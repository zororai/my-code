<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class SeedMissingSidebarSettingsPermissions extends Migration
{
    private $permissions = [
        'sidebar-settings',
        'sidebar-settings-theme',
    ];

    public function up()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function down()
    {
        foreach ($this->permissions as $name) {
            Permission::where('name', $name)->delete();
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'create-complaint',
            'view-complaint',
            'update-complaint',
            'delete-complaint',
            'assign-complaint',
            'add-complaint-notes',
            'add-attachment',
            'view-attachment',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $citizen = Role::firstOrCreate(['name' => 'citizen']);
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);

        $citizen->givePermissionTo(['create-complaint', 'view-complaint']);
      $employee->givePermissionTo([
    'view-complaint', 
    'update-complaint', 
    'assign-complaint', 
    'add-complaint-notes',
    'add-attachment',
    'view-attachment'
]);
        $superAdmin->givePermissionTo(Permission::all());
    }
}

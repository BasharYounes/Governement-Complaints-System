<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // $permissions = [
        //     'create-complaint',
        //     'view-complaint',
        //     'update-complaint',
        //     'delete-complaint',
        //     'assign-complaint',
        //     'add-complaint-notes',
        //     'add-attachment',
        //     'view-attachment',
        //     'manage-users',
        //     // Admin-specific permissions
        //     'view-all-complaints',
        //     'view-employees',
        //     'view-complaint-audit-logs',
        //     'view-statistics',
        //     'view-all-complaint-logs',
        // ];

        // foreach ($permissions as $permission) {
        //     Permission::firstOrCreate(['name' => $permission]);
        // }

        // $citizen = Role::firstOrCreate(['name' => 'citizen']);
        // $employee = Role::firstOrCreate(['name' => 'employee']);
        // $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);

        // $citizen->givePermissionTo(['create-complaint', 'view-complaint']);

        // $employee->givePermissionTo([
        //     'view-complaint',
        //     'update-complaint',
        //     'assign-complaint',
        //     'add-complaint-notes',
        //     'add-attachment',
        //     'view-attachment'
        // ]);

        // $superAdmin->givePermissionTo(Permission::all()); // super_admin يحصل على كل الصلاحيات

        // ----- Admin ------
        $admin = Role::create(['name' => 'super_admin', 'guard_name' => 'admin-api']);

        $adminPermissions = [
            'view-all-complaints',
            'view-employees',
            'view-statistics',
            'export-monthly-pdf',
            'export-monthly-csv'
        ];

        foreach ($adminPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'admin-api']);
        }

        $admin->givePermissionTo(Permission::where('guard_name', 'admin-api')->get());

        // ----- Employee ------
        $employee = Role::create(['name' => 'employee', 'guard_name' => 'employee-api']);

        $employeePermissions = [
            'view-complaint',
            'update-complaint',
            'add-complaint-notes',
        ];

        foreach ($employeePermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'employee-api']);
        }

        $employee->givePermissionTo(Permission::where('guard_name', 'employee-api')->get());

        // ----- Citizen ------
        Role::firstOrCreate(['name' => 'citizen', 'guard_name' => 'api']);
    }
}

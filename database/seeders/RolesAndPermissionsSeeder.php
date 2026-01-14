<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage users',
            'view dashboard',
            'manage clients',
            'manage fournisseurs',
            'manage achats',
            'manage ventes',
            'manage stock',
            'manage tresorerie',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        // Super admin gets all permissions
        $superAdminRole->syncPermissions(Permission::all());

        // Commercial
        $commercialRole = Role::firstOrCreate(['name' => 'commercial']);
        $commercialRole->syncPermissions([
            'view dashboard',
            'manage clients',
            'manage ventes',
        ]);

        // Assistant
        $assistantRole = Role::firstOrCreate(['name' => 'assistant']);
        $assistantRole->syncPermissions([
            'view dashboard',
            'manage achats',
            'manage fournisseurs',
            'manage stock',
        ]);

        // Assign superadmin role to existing admin user
        $admin = User::where('email', 'admin@bouyahya.com')->first();
        if ($admin) {
            $admin->assignRole('superadmin');
        }
    }
}

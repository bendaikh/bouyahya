<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define the mapping between legacy permissions and their granular prefixes
        $permissionMappings = [
            'manage achats' => 'achats.',
            'manage ventes' => 'ventes.',
            'manage stock' => 'stock.',
            'manage tresorerie' => 'tresorerie.',
            'manage clients' => 'contacts.clients.',
            'manage fournisseurs' => 'contacts.fournisseurs.',
            'manage users' => ['admin.users.', 'admin.roles.'],
            'manage settings' => 'admin.settings.',
        ];

        // Use Gate::before to intercept all permission checks
        // This runs BEFORE Spatie's permission check
        Gate::before(function ($user, $ability) use ($permissionMappings) {
            // Superadmin bypass - always allow
            if ($user->role === 'superadmin') {
                return true;
            }
            
            if ($user->roles && $user->roles->contains('name', 'superadmin')) {
                return true;
            }

            // Get all user permissions
            $userPermissions = [];
            if ($user->roles) {
                foreach ($user->roles as $role) {
                    if ($role->permissions) {
                        foreach ($role->permissions as $permission) {
                            $userPermissions[] = is_string($permission) ? $permission : $permission->name;
                        }
                    }
                }
            }

            // If checking a legacy permission, also accept granular permissions
            if (isset($permissionMappings[$ability])) {
                // Check direct legacy permission first
                if (in_array($ability, $userPermissions)) {
                    return true;
                }

                // Check if user has any granular permission for this module
                $prefixes = is_array($permissionMappings[$ability]) ? $permissionMappings[$ability] : [$permissionMappings[$ability]];
                foreach ($userPermissions as $perm) {
                    foreach ($prefixes as $prefix) {
                        if (str_starts_with($perm, $prefix)) {
                            return true;
                        }
                    }
                }
            }

            // If checking a granular permission, also check if user has the legacy parent permission
            foreach ($permissionMappings as $legacyPerm => $prefixes) {
                $prefixList = is_array($prefixes) ? $prefixes : [$prefixes];
                foreach ($prefixList as $prefix) {
                    if (str_starts_with($ability, $prefix)) {
                        // User is checking a granular permission
                        // Allow if they have the legacy parent permission
                        if (in_array($legacyPerm, $userPermissions)) {
                            return true;
                        }
                        break 2;
                    }
                }
            }

            // Return null to let Spatie's normal permission check run
            return null;
        });
    }
}

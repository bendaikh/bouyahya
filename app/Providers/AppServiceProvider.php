<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::share('appName', Setting::getAppName());

        // Define the mapping between legacy permissions and their granular prefixes
        // This is only used for checking legacy permissions (like route middleware)
        // NOT for granting granular permissions automatically
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

            // If checking a legacy permission (like 'manage ventes' from route middleware),
            // also accept if user has ANY granular permission for that module
            // This allows users with granular permissions to access the module routes
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

            // IMPORTANT: Do NOT grant granular permissions based on legacy permissions
            // Each granular permission must be explicitly assigned
            // This ensures that if superadmin unchecks 'ventes.bon-commande.create',
            // the user won't be able to create even if they have 'manage ventes'

            // Return null to let Spatie's normal permission check run
            return null;
        });
    }
}

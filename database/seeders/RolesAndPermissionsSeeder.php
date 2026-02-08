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

        // Define permission hierarchy
        $permissions = [
            // Dashboard
            'view dashboard',

            // Achats
            'achats.bon-commande.view',
            'achats.bon-commande.create',
            'achats.bon-commande.edit',
            'achats.bon-commande.delete',
            'achats.bon-commande.print',
            'achats.bon-commande.validate',
            'achats.bon-commande.convert',
            
            'achats.bon-achat.view',
            'achats.bon-achat.create',
            'achats.bon-achat.edit',
            'achats.bon-achat.delete',
            
            'achats.reglement.view',
            'achats.reglement.create',
            'achats.reglement.edit',
            'achats.reglement.delete',
            
            'achats.historique.view',
            'achats.releve.view',
            'achats.echeancier.view',

            // Ventes
            'ventes.bon-commande.view',
            'ventes.bon-commande.create',
            'ventes.bon-commande.edit',
            'ventes.bon-commande.delete',
            'ventes.bon-commande.print',
            'ventes.bon-commande.validate',
            'ventes.bon-commande.cancel',
            'ventes.bon-commande.convert',

            'ventes.bon-livraison.view',
            'ventes.bon-livraison.create',
            'ventes.bon-livraison.edit',
            'ventes.bon-livraison.delete',
            'ventes.bon-livraison.print',
            'ventes.bon-livraison.validate',

            'ventes.reglement.view',
            'ventes.reglement.create',
            'ventes.reglement.delete',

            'ventes.historique.view',
            'ventes.releve.view',

            // Stock
            'stock.articles.view',
            'stock.articles.create',
            'stock.articles.edit',
            'stock.articles.delete',
            'stock.mouvement.view',
            'stock.etat.view',

            // Trésorerie
            'tresorerie.etat-journalier.view',
            'tresorerie.releve-reglements.view',
            'tresorerie.balance-caisse.view',
            'tresorerie.impots.view',
            'tresorerie.compte-bancaire.view',
            'tresorerie.compte-bancaire.create',
            'tresorerie.compte-bancaire.delete',
            'tresorerie.encaissement.view',
            'tresorerie.charges.view',

            // Contacts
            'contacts.clients.view',
            'contacts.clients.create',
            'contacts.clients.edit',
            'contacts.clients.delete',
            'contacts.fournisseurs.view',
            'contacts.fournisseurs.create',
            'contacts.fournisseurs.edit',
            'contacts.fournisseurs.delete',

            // Administration
            'admin.users.view',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',
            'admin.roles.view',
            'admin.roles.manage',
            'admin.settings.view',
            'admin.settings.manage',

            // Legacy permissions (for compatibility during transition)
            'manage users',
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
        $superAdminRole->syncPermissions(Permission::all());

        // Commercial
        $commercialRole = Role::firstOrCreate(['name' => 'commercial']);
        $commercialRole->syncPermissions([
            'view dashboard',
            'ventes.bon-commande.view',
            'ventes.bon-commande.create',
            'ventes.bon-commande.edit',
            'ventes.bon-livraison.view',
            'ventes.bon-livraison.create',
            'contacts.clients.view',
            'contacts.clients.create',
            'manage ventes', // legacy
            'manage clients', // legacy
        ]);

        // Assistant
        $assistantRole = Role::firstOrCreate(['name' => 'assistant']);
        $assistantRole->syncPermissions([
            'view dashboard',
            'achats.bon-commande.view',
            'achats.bon-commande.create',
            'achats.bon-achat.view',
            'achats.bon-achat.create',
            'stock.articles.view',
            'contacts.fournisseurs.view',
            'manage achats', // legacy
            'manage fournisseurs', // legacy
            'manage stock', // legacy
        ]);

        // Assign superadmin role to existing admin user
        $admin = User::where('email', 'admin@bouyahya.com')->first();
        if ($admin) {
            $admin->assignRole('superadmin');
        }
    }
}

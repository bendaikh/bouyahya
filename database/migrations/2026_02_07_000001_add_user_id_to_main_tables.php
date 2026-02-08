<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get the superadmin user ID (will be used as default for existing records)
        $superadminId = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'superadmin')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->value('users.id');

        if (!$superadminId) {
            // Fallback to first user if no superadmin found
            $superadminId = DB::table('users')->orderBy('id')->value('id');
        }

        // Add user_id to bon_commande_clients
        if (Schema::hasTable('bon_commande_clients')) {
            Schema::table('bon_commande_clients', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            // Set existing records to superadmin
            DB::table('bon_commande_clients')->update(['user_id' => $superadminId]);
        }

        // Add user_id to bon_commande_fournisseurs
        if (Schema::hasTable('bon_commande_fournisseurs')) {
            Schema::table('bon_commande_fournisseurs', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            DB::table('bon_commande_fournisseurs')->update(['user_id' => $superadminId]);
        }

        // Add user_id to bon_livraison_clients
        if (Schema::hasTable('bon_livraison_clients')) {
            Schema::table('bon_livraison_clients', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            DB::table('bon_livraison_clients')->update(['user_id' => $superadminId]);
        }

        // Add user_id to bon_achat_fournisseur
        if (Schema::hasTable('bon_achat_fournisseur')) {
            Schema::table('bon_achat_fournisseur', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            DB::table('bon_achat_fournisseur')->update(['user_id' => $superadminId]);
        }

        // Add user_id to reglements_clients
        if (Schema::hasTable('reglements_clients')) {
            Schema::table('reglements_clients', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            DB::table('reglements_clients')->update(['user_id' => $superadminId]);
        }

        // Add user_id to reglements_fournisseurs
        if (Schema::hasTable('reglements_fournisseurs')) {
            Schema::table('reglements_fournisseurs', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            DB::table('reglements_fournisseurs')->update(['user_id' => $superadminId]);
        }

        // Add user_id to clients
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            DB::table('clients')->update(['user_id' => $superadminId]);
        }

        // Add user_id to fournisseurs
        if (Schema::hasTable('fournisseurs')) {
            Schema::table('fournisseurs', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
            DB::table('fournisseurs')->update(['user_id' => $superadminId]);
        }
    }

    public function down(): void
    {
        $tables = [
            'bon_commande_clients',
            'bon_commande_fournisseurs',
            'bon_livraison_clients',
            'bon_achat_fournisseur',
            'reglements_clients',
            'reglements_fournisseurs',
            'clients',
            'fournisseurs'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['user_id']);
                    $blueprint->dropColumn('user_id');
                });
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bon_livraison_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('bon_livraison_clients', 'fournisseur_id')) {
                $table->unsignedBigInteger('fournisseur_id')->nullable()->after('bon_achat_fournisseur_id');
                $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('set null');
            }
            if (!Schema::hasColumn('bon_livraison_clients', 'code_fournisseur')) {
                $table->string('code_fournisseur')->nullable()->after('fournisseur_id');
            }
            if (!Schema::hasColumn('bon_livraison_clients', 'nom_fournisseur')) {
                $table->string('nom_fournisseur')->nullable()->after('code_fournisseur');
            }
            if (!Schema::hasColumn('bon_livraison_clients', 'bon_fournisseur_numero')) {
                $table->string('bon_fournisseur_numero')->nullable()->after('nom_fournisseur');
            }
            if (!Schema::hasColumn('bon_livraison_clients', 'type_reglement')) {
                $table->string('type_reglement')->nullable()->after('mode_reglement');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_livraison_clients', function (Blueprint $table) {
            if (Schema::hasColumn('bon_livraison_clients', 'fournisseur_id')) {
                $table->dropForeign(['fournisseur_id']);
                $table->dropColumn('fournisseur_id');
            }
            if (Schema::hasColumn('bon_livraison_clients', 'code_fournisseur')) {
                $table->dropColumn('code_fournisseur');
            }
            if (Schema::hasColumn('bon_livraison_clients', 'nom_fournisseur')) {
                $table->dropColumn('nom_fournisseur');
            }
            if (Schema::hasColumn('bon_livraison_clients', 'bon_fournisseur_numero')) {
                $table->dropColumn('bon_fournisseur_numero');
            }
            if (Schema::hasColumn('bon_livraison_clients', 'type_reglement')) {
                $table->dropColumn('type_reglement');
            }
        });
    }
};

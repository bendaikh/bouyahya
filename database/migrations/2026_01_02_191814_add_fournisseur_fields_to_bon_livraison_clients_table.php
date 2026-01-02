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
            $table->unsignedBigInteger('fournisseur_id')->nullable()->after('bon_achat_fournisseur_id');
            $table->string('code_fournisseur')->nullable()->after('fournisseur_id');
            $table->string('nom_fournisseur')->nullable()->after('code_fournisseur');
            $table->string('bon_fournisseur_numero')->nullable()->after('nom_fournisseur');
            $table->string('type_reglement')->nullable()->after('mode_reglement');
            
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_livraison_clients', function (Blueprint $table) {
            $table->dropForeign(['fournisseur_id']);
            $table->dropColumn(['fournisseur_id', 'code_fournisseur', 'nom_fournisseur', 'bon_fournisseur_numero', 'type_reglement']);
        });
    }
};

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
            $table->foreignId('bon_achat_fournisseur_id')->nullable()->after('bon_commande_id')->constrained('bon_achat_fournisseur')->onDelete('set null');
            $table->string('mode_reglement')->nullable()->after('mode_paiement');
            $table->string('delai_reglement')->nullable()->after('mode_reglement');
            $table->string('transporteur')->nullable()->after('delai_reglement');
            $table->string('commercial')->nullable()->after('transporteur');
            $table->string('situation')->nullable()->after('commercial');
            $table->string('vehicule')->nullable()->after('situation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_livraison_clients', function (Blueprint $table) {
            $table->dropForeign(['bon_achat_fournisseur_id']);
            $table->dropColumn([
                'bon_achat_fournisseur_id',
                'mode_reglement',
                'delai_reglement',
                'transporteur',
                'commercial',
                'situation',
                'vehicule'
            ]);
        });
    }
};

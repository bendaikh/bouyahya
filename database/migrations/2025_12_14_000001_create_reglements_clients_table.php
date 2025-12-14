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
        // Table principale des règlements clients
        Schema::create('reglements_clients', function (Blueprint $table) {
            $table->id();
            $table->string('code_reglement')->unique(); // RC-0001
            $table->date('date_reglement');
            $table->unsignedBigInteger('client_id');
            $table->string('type_reglement'); // Virement, Chèque, Espèces, Traite
            $table->string('numero_piece')->nullable(); // Numéro chèque/virement
            $table->string('banque')->nullable();
            $table->string('nom_tire')->nullable(); // Nom de tiré
            $table->unsignedBigInteger('tresorerie_id')->nullable(); // Compte de trésorerie
            $table->decimal('montant', 12, 2);
            $table->date('date_encaissement')->nullable();
            $table->enum('statut', ['instance', 'paye', 'reporte', 'cour', 'impaye', 'devalide'])->default('impaye');
            $table->enum('etat_remboursement', ['devalide', 'impaye'])->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('tresorerie_id')->references('id')->on('compte_tresoreries')->onDelete('set null');
        });
        
        // Table des ventilations (lignes de règlement sur les bons de livraison)
        Schema::create('reglement_client_lignes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reglement_id');
            $table->unsignedBigInteger('bon_livraison_id');
            $table->decimal('montant_regle', 12, 2);
            $table->timestamps();
            
            $table->foreign('reglement_id')->references('id')->on('reglements_clients')->onDelete('cascade');
            $table->foreign('bon_livraison_id')->references('id')->on('bon_livraison_clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglement_client_lignes');
        Schema::dropIfExists('reglements_clients');
    }
};


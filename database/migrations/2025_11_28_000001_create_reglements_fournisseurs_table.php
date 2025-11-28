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
        // Table principale des règlements fournisseurs
        Schema::create('reglements_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('code_reglement')->unique(); // RF-0001
            $table->date('date_reglement');
            $table->unsignedBigInteger('fournisseur_id');
            $table->string('type_reglement'); // Virement, Chèque, Espèces, Traite
            $table->string('numero_piece')->nullable(); // Numéro chèque/virement
            $table->string('banque')->nullable();
            $table->string('nom_beneficiaire')->nullable();
            $table->decimal('montant', 12, 2);
            $table->date('date_encaissement')->nullable();
            $table->enum('statut', ['paye', 'impaye', 'reporte'])->default('impaye');
            $table->text('observation')->nullable();
            $table->timestamps();
            
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('cascade');
        });
        
        // Table des ventilations (lignes de règlement sur les bons d'achat)
        Schema::create('reglement_fournisseur_lignes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reglement_id');
            $table->unsignedBigInteger('bon_achat_id');
            $table->decimal('montant_regle', 12, 2);
            $table->timestamps();
            
            $table->foreign('reglement_id')->references('id')->on('reglements_fournisseurs')->onDelete('cascade');
            $table->foreign('bon_achat_id')->references('id')->on('bon_achat_fournisseur')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglement_fournisseur_lignes');
        Schema::dropIfExists('reglements_fournisseurs');
    }
};


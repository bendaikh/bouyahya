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
        Schema::create('bon_achat_fournisseur', function (Blueprint $table) {
            $table->id();
            $table->string('numero_bon')->unique(); // BF-2024001
            $table->date('date');
            $table->unsignedBigInteger('fournisseur_id');
            $table->string('type_paiement'); // Chèque, Espèces, Virement, etc.
            $table->string('echeance'); // 30 jours, 60 jours, etc.
            $table->string('client_livre')->nullable();
            $table->string('famille');
            $table->string('ville')->nullable();
            $table->string('chauffeur')->nullable();
            $table->string('matricule')->nullable();
            $table->decimal('sous_total_ttc', 12, 2)->default(0);
            $table->integer('total_qte')->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->enum('statut', ['brouillon', 'valide', 'annule'])->default('brouillon');
            $table->timestamps();
            
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('cascade');
        });
        
        // Table for articles in each bon d'achat
        Schema::create('bon_achat_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bon_achat_id');
            $table->string('ref_article');
            $table->string('designation_article');
            $table->integer('qte');
            $table->decimal('prix_unitaire_ttc', 10, 2);
            $table->decimal('total', 10, 2); // qte * prix_unitaire_ttc
            $table->timestamps();
            
            $table->foreign('bon_achat_id')->references('id')->on('bon_achat_fournisseur')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_achat_articles');
        Schema::dropIfExists('bon_achat_fournisseur');
    }
};

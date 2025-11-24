<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bon_commande_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('numero_bon')->unique();
            $table->date('date');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->onDelete('cascade');
            $table->string('mode_paiement');
            $table->string('echeance');
            $table->integer('total_quantites')->default(0);
            $table->decimal('total_general', 10, 2)->default(0);
            $table->string('statut')->default('En attente');
            $table->timestamps();
        });

        Schema::create('bon_commande_fournisseur_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bon_commande_fournisseur_id');
            $table->string('code_article');
            $table->string('designation');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('sous_total', 10, 2);
            $table->timestamps();
            
            $table->foreign('bon_commande_fournisseur_id', 'bcf_articles_bcf_id_foreign')
                  ->references('id')
                  ->on('bon_commande_fournisseurs')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_commande_fournisseur_articles');
        Schema::dropIfExists('bon_commande_fournisseurs');
    }
};


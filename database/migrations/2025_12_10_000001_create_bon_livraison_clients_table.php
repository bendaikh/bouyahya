<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new fields to bon_commande_clients
        Schema::table('bon_commande_clients', function (Blueprint $table) {
            $table->string('ville_livraison')->nullable()->after('echeance');
            $table->text('motif_annulation')->nullable()->after('statut');
        });

        // Create bon_livraison_clients table
        Schema::create('bon_livraison_clients', function (Blueprint $table) {
            $table->id();
            $table->string('numero_bon')->unique();
            $table->date('date');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('bon_commande_id')->nullable()->constrained('bon_commande_clients')->onDelete('set null');
            $table->string('mode_paiement');
            $table->string('echeance')->nullable();
            $table->date('date_echeance')->nullable();
            $table->string('ville_livraison')->nullable();
            $table->string('chauffeur')->nullable();
            $table->string('matricule_vehicule')->nullable();
            $table->string('telephone_chauffeur')->nullable();
            $table->text('adresse_livraison')->nullable();
            $table->text('observations')->nullable();
            $table->integer('total_quantites')->default(0);
            $table->decimal('total_general', 10, 2)->default(0);
            $table->string('statut')->default('En attente'); // En attente, Livré, Annulé
            $table->timestamps();
        });

        // Create bon_livraison_client_articles table
        Schema::create('bon_livraison_client_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bon_livraison_client_id');
            $table->string('code_article');
            $table->string('designation');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('sous_total', 10, 2);
            $table->timestamps();
            
            $table->foreign('bon_livraison_client_id', 'blc_articles_blc_id_foreign')
                  ->references('id')
                  ->on('bon_livraison_clients')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_livraison_client_articles');
        Schema::dropIfExists('bon_livraison_clients');
        
        Schema::table('bon_commande_clients', function (Blueprint $table) {
            $table->dropColumn(['ville_livraison', 'motif_annulation']);
        });
    }
};


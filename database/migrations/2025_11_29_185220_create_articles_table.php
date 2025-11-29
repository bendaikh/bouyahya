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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // Référence Article
            $table->string('designation'); // Désignation Article
            $table->string('famille_id')->nullable(); // ID de la famille (from settings)
            $table->string('sous_famille_id')->nullable(); // ID de la sous-famille (from settings)
            $table->string('unite_mesure_id')->nullable(); // ID de l'unité de mesure (from settings)
            $table->decimal('tva', 5, 2)->default(20); // TVA (%)
            $table->boolean('autoriser_stock_negatif')->default(false); // Autoriser stock négatif
            $table->decimal('prix_achat', 12, 2)->default(0); // Prix d'achat
            $table->decimal('prix_vente', 12, 2)->default(0); // Prix de vente
            $table->integer('stock_actuel')->default(0); // Stock actuel
            $table->integer('stock_minimum')->default(0); // Stock minimum (alerte)
            $table->boolean('actif')->default(true); // Article actif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

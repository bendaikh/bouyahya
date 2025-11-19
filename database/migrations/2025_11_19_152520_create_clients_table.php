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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code_client')->unique();
            $table->string('raison_sociale');
            $table->string('nom_gerant');
            $table->string('ville');
            $table->string('type_client'); // 'Particulier' or 'Société'
            $table->string('mode_paiement'); // 'Espèces', 'Virement', 'Chèque', 'Traite'
            $table->string('echeance'); // '0j', '30j', '45j', '60j', '90j'
            $table->string('cin')->nullable();
            $table->string('if_fiscal')->nullable();
            $table->string('patente')->nullable();
            $table->string('cnss')->nullable();
            $table->string('ice')->nullable();
            $table->string('banque')->nullable();
            $table->string('rib')->nullable();
            $table->decimal('plafond', 15, 2)->nullable();
            $table->boolean('bloquer')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

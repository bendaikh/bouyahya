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
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('code_fournisseur')->unique();
            $table->string('nom_fournisseur');
            $table->string('nom_gerant');
            $table->string('telephone');
            $table->string('email')->nullable();
            $table->string('activite')->nullable();
            $table->string('ville')->nullable();
            $table->string('ice')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};

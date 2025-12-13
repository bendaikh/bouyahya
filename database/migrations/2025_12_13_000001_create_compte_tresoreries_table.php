<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compte_tresoreries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // CT-2025/0001
            $table->date('date_creation');
            $table->string('libelle');
            $table->enum('type_compte', ['banque', 'caisse'])->default('banque');
            $table->string('agence')->nullable();
            $table->string('ville')->nullable();
            $table->text('adresse')->nullable();
            $table->decimal('solde_initial', 12, 2)->default(0);
            $table->decimal('solde_actuel', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compte_tresoreries');
    }
};



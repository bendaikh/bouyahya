<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table for charge types (categories)
        Schema::create('types_charges', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Table for charge entries (the actual charges)
        Schema::create('charge_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // CHG-2025/0001
            $table->date('date');
            $table->time('heure');
            $table->string('operateur')->nullable();
            $table->foreignId('compte_caisse_id')->nullable()->constrained('compte_tresoreries')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('types_charges')->nullOnDelete();
            $table->string('numero')->nullable();
            $table->string('libelle');
            $table->string('beneficiaire')->nullable();
            $table->decimal('montant', 12, 2)->default(0);
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_entries');
        Schema::dropIfExists('types_charges');
    }
};


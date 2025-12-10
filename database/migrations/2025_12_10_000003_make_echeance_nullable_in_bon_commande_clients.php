<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_commande_clients', function (Blueprint $table) {
            $table->string('echeance')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bon_commande_clients', function (Blueprint $table) {
            $table->string('echeance')->nullable(false)->change();
        });
    }
};


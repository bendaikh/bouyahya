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
        Schema::table('bon_achat_fournisseur', function (Blueprint $table) {
            $table->foreignId('bon_commande_id')->nullable()->after('statut')->constrained('bon_commande_fournisseurs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_achat_fournisseur', function (Blueprint $table) {
            $table->dropForeign(['bon_commande_id']);
            $table->dropColumn('bon_commande_id');
        });
    }
};


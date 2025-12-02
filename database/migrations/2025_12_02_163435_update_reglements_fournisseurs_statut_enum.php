<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include all new status values
        DB::statement("ALTER TABLE reglements_fournisseurs MODIFY COLUMN statut ENUM('instance', 'paye', 'reporte', 'cour', 'impaye', 'devalide') DEFAULT 'impaye'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE reglements_fournisseurs MODIFY COLUMN statut ENUM('paye', 'impaye', 'reporte') DEFAULT 'impaye'");
    }
};

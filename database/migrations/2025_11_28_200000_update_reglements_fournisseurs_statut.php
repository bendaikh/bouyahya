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
        // First, update existing data to new status values
        DB::statement("UPDATE reglements_fournisseurs SET statut = 'brouillon' WHERE statut = 'brouillon'"); // Keep as is for now
        
        // Change the enum column to accept new values
        DB::statement("ALTER TABLE reglements_fournisseurs MODIFY COLUMN statut ENUM('paye', 'impaye', 'reporte', 'brouillon', 'valide', 'annule') DEFAULT 'impaye'");
        
        // Now update old values to new ones
        DB::statement("UPDATE reglements_fournisseurs SET statut = 'impaye' WHERE statut IN ('brouillon', 'annule')");
        DB::statement("UPDATE reglements_fournisseurs SET statut = 'paye' WHERE statut = 'valide'");
        
        // Finally, restrict to only new values
        DB::statement("ALTER TABLE reglements_fournisseurs MODIFY COLUMN statut ENUM('paye', 'impaye', 'reporte') DEFAULT 'impaye'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old enum values
        DB::statement("ALTER TABLE reglements_fournisseurs MODIFY COLUMN statut ENUM('paye', 'impaye', 'reporte', 'brouillon', 'valide', 'annule') DEFAULT 'impaye'");
        
        DB::statement("UPDATE reglements_fournisseurs SET statut = 'brouillon' WHERE statut = 'impaye'");
        DB::statement("UPDATE reglements_fournisseurs SET statut = 'valide' WHERE statut = 'paye'");
        DB::statement("UPDATE reglements_fournisseurs SET statut = 'brouillon' WHERE statut = 'reporte'");
        
        DB::statement("ALTER TABLE reglements_fournisseurs MODIFY COLUMN statut ENUM('brouillon', 'valide', 'annule') DEFAULT 'brouillon'");
    }
};


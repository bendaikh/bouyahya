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
        Schema::table('charge_entries', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['type_id']);
        });

        Schema::table('charge_entries', function (Blueprint $table) {
            // Change type_id from unsigned big integer to string
            // to store settings-based type IDs like "tr_1", "tr_2"
            $table->string('type_id', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This rollback may fail if there are string values in type_id
        Schema::table('charge_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->change();
            $table->foreign('type_id')->references('id')->on('types_charges')->nullOnDelete();
        });
    }
};


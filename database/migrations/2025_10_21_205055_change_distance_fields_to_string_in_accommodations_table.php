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
        Schema::table('accommodations', function (Blueprint $table) {
            // Change distance fields from integer to string to accept text values
            $table->string('distance_to_water_m', 255)->nullable()->change();
            $table->string('distance_to_boat_berth_m', 255)->nullable()->change();
            $table->string('distance_to_parking_m', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Revert back to integer fields
            $table->integer('distance_to_water_m')->nullable()->change();
            $table->integer('distance_to_boat_berth_m')->nullable()->change();
            $table->integer('distance_to_parking_m')->nullable()->change();
        });
    }
};
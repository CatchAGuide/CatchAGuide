<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAccommodationTablesColumnNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update accommodation_types table
        Schema::table('accommodation_types', function (Blueprint $table) {
            $table->renameColumn('value', 'name');
            $table->renameColumn('value_de', 'name_en');
        });

        // Update accommodation_details table
        Schema::table('accommodation_details', function (Blueprint $table) {
            $table->renameColumn('value', 'name');
            $table->renameColumn('value_de', 'name_en');
        });

        // Update room_configurations table
        Schema::table('room_configurations', function (Blueprint $table) {
            $table->renameColumn('value', 'name');
            $table->renameColumn('value_de', 'name_en');
        });

        // Update facilities table
        Schema::table('facilities', function (Blueprint $table) {
            $table->renameColumn('value', 'name');
            $table->renameColumn('value_de', 'name_en');
        });

        // Update kitchen_equipment table
        Schema::table('kitchen_equipment', function (Blueprint $table) {
            $table->renameColumn('value', 'name');
            $table->renameColumn('value_de', 'name_en');
        });

        // Update bathroom_amenities table
        Schema::table('bathroom_amenities', function (Blueprint $table) {
            $table->renameColumn('value', 'name');
            $table->renameColumn('value_de', 'name_en');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse accommodation_types table
        Schema::table('accommodation_types', function (Blueprint $table) {
            $table->renameColumn('name', 'value');
            $table->renameColumn('name_en', 'value_de');
        });

        // Reverse accommodation_details table
        Schema::table('accommodation_details', function (Blueprint $table) {
            $table->renameColumn('name', 'value');
            $table->renameColumn('name_en', 'value_de');
        });

        // Reverse room_configurations table
        Schema::table('room_configurations', function (Blueprint $table) {
            $table->renameColumn('name', 'value');
            $table->renameColumn('name_en', 'value_de');
        });

        // Reverse facilities table
        Schema::table('facilities', function (Blueprint $table) {
            $table->renameColumn('name', 'value');
            $table->renameColumn('name_en', 'value_de');
        });

        // Reverse kitchen_equipment table
        Schema::table('kitchen_equipment', function (Blueprint $table) {
            $table->renameColumn('name', 'value');
            $table->renameColumn('name_en', 'value_de');
        });

        // Reverse bathroom_amenities table
        Schema::table('bathroom_amenities', function (Blueprint $table) {
            $table->renameColumn('name', 'value');
            $table->renameColumn('name_en', 'value_de');
        });
    }
}

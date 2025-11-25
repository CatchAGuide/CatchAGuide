<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAccommodationFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('condition_or_style');
            $table->dropColumn('living_area_sqm');
            $table->dropColumn('floor_layout');
            $table->dropColumn('number_of_bedrooms');
            $table->dropColumn('bed_types');
            $table->dropColumn('price_type');
            $table->dropColumn('kitchen_type');
            $table->dropColumn('bathroom');
            $table->dropColumn('location_description');
            $table->dropColumn('distance_to_nearest_town_km');
            $table->dropColumn('distance_to_airport_km');
            $table->dropColumn('distance_to_ferry_port_km');
            $table->dropColumn('changeover_day');
            $table->dropColumn('price_per_night');
            $table->dropColumn('price_per_week');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

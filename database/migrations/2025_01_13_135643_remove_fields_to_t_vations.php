<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFieldsToTVations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropColumn('accommodation_description');
            $table->dropColumn('amenities');
            $table->dropColumn('living_area');
            $table->dropColumn('bedroom_count');
            $table->dropColumn('bed_count');
            $table->dropColumn('max_persons');
            $table->dropColumn('catering_info');
            $table->dropColumn('min_rental_days');
            $table->dropColumn('basic_fishing_description');
            $table->dropColumn('boat_description');
            $table->dropColumn('equipment');
            $table->dropColumn('package_price_per_person');
            $table->dropColumn('accommodation_price');
            $table->dropColumn('boat_rental_price');
            $table->dropColumn('guiding_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
        });
    }
}

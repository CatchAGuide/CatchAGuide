<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccommodationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail_path')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('location');
            $table->string('city');
            $table->string('country');
            $table->string('region');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->text('description')->nullable();
            $table->string('accommodation_type');
            $table->string('condition_or_style')->nullable();
            $table->integer('living_area_sqm')->nullable();
            $table->string('floor_layout')->nullable();
            $table->integer('max_occupancy')->nullable();
            $table->integer('number_of_bedrooms')->nullable();
            $table->integer('number_of_beds')->nullable();
            $table->json('bed_types')->nullable();
            $table->boolean('living_room')->default(false);
            $table->boolean('dining_room_or_area')->default(false);
            $table->boolean('terrace')->default(false);
            $table->boolean('garden')->default(false);
            $table->boolean('swimming_pool')->default(false);
            $table->string('kitchen_type')->nullable();
            $table->boolean('refrigerator_freezer')->default(false);
            $table->boolean('oven')->default(false);
            $table->boolean('stove_or_ceramic_hob')->default(false);
            $table->boolean('microwave')->default(false);
            $table->boolean('dishwasher')->default(false);
            $table->boolean('coffee_machine')->default(false);
            $table->boolean('cookware_and_dishes')->default(false);
            $table->integer('bathroom')->nullable();
            $table->boolean('washing_machine')->default(false);
            $table->boolean('dryer')->default(false);
            $table->boolean('separate_laundry_room')->default(false);
            $table->boolean('freezer_room')->default(false);
            $table->boolean('filleting_house')->default(false);
            $table->boolean('wifi_or_internet')->default(false);
            $table->boolean('bed_linen_included')->default(false);
            $table->boolean('utilities_included')->default(false);
            $table->boolean('pets_allowed')->default(false);
            $table->boolean('smoking_allowed')->default(false);
            $table->boolean('reception_available')->default(false);
            $table->text('location_description')->nullable();
            $table->integer('distance_to_water_m')->nullable();
            $table->integer('distance_to_boat_berth_m')->nullable();
            $table->decimal('distance_to_shop_km', 8, 2)->nullable();
            $table->integer('distance_to_parking_m')->nullable();
            $table->decimal('distance_to_nearest_town_km', 8, 2)->nullable();
            $table->decimal('distance_to_airport_km', 8, 2)->nullable();
            $table->decimal('distance_to_ferry_port_km', 8, 2)->nullable();
            $table->string('changeover_day')->nullable();
            $table->integer('minimum_stay_nights')->nullable();
            $table->json('rental_includes')->nullable();
            $table->decimal('price_per_night', 10, 2)->nullable();
            $table->decimal('price_per_week', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accommodations');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVacationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('location');
            $table->text('city');
            $table->text('country');
            $table->text('latitude');
            $table->text('longitude');
            $table->text('region');
            $table->json('gallery')->nullable();
            $table->text('best_travel_times');
            $table->text('surroundings_description');
            $table->json('target_fish');
            $table->text('airport_distance')->nullable();
            $table->text('water_distance')->nullable(); 
            $table->text('shopping_distance')->nullable();
            $table->text('travel_included')->nullable();
            $table->json('travel_options')->nullable();
            $table->text('pets_allowed')->nullable();
            $table->text('smoking_allowed')->nullable();
            $table->text('disability_friendly')->nullable();
            $table->text('accommodation_description')->nullable();
            $table->text('living_area')->nullable();
            $table->text('bedroom_count')->nullable();
            $table->text('bed_count')->nullable();
            $table->text('max_persons')->nullable();
            $table->text('min_rental_days')->nullable();
            $table->json('amenities')->nullable(); // Store array of amenities
            $table->text('boat_description')->nullable();
            $table->json('equipment')->nullable(); // Store array of equipment
            $table->text('basic_fishing_description')->nullable();
            $table->text('catering_info')->nullable();
            $table->text('package_price_per_person')->nullable();
            $table->text('accommodation_price')->nullable();
            $table->text('boat_rental_price')->nullable();
            $table->text('guiding_price')->nullable();
            $table->json('additional_services')->nullable(); // Store array of additional services
            $table->json('included_services')->nullable(); // Store array of included services
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vacations');
    }
}

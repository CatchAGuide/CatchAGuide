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
            $table->integer('airport_distance')->nullable();
            $table->integer('water_distance')->nullable(); 
            $table->integer('shopping_distance')->nullable();
            $table->boolean('travel_included')->default(false);
            $table->json('travel_options')->nullable();
            $table->boolean('pets_allowed')->default(false);
            $table->boolean('smoking_allowed')->default(false);
            $table->boolean('disability_friendly')->default(false);
            $table->text('accommodation_description')->nullable();
            $table->text('living_area')->nullable();
            $table->text('bedroom_count')->nullable();
            $table->text('bed_count')->nullable();
            $table->integer('max_persons')->nullable();
            $table->integer('min_rental_days')->nullable();
            $table->json('amenities')->nullable(); // Store array of amenities
            $table->text('boat_description')->nullable();
            $table->json('equipment')->nullable(); // Store array of equipment
            $table->text('basic_fishing_description')->nullable();
            $table->text('catering_info')->nullable();
            $table->decimal('package_price_per_person', 10, 2)->nullable();
            $table->decimal('accommodation_price', 10, 2)->nullable();
            $table->decimal('boat_rental_price', 10, 2)->nullable();
            $table->decimal('guiding_price', 10, 2)->nullable();
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

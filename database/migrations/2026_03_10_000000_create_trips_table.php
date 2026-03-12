<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            // Basic identity & media
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->json('gallery_images')->nullable();

            // Fishing details
            $table->json('target_species')->nullable();
            $table->json('fishing_methods')->nullable();
            $table->string('fishing_style')->nullable();
            $table->json('water_types')->nullable();
            $table->string('skill_level')->nullable();

            // General details
            $table->unsignedInteger('duration_nights')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('group_size_min')->nullable();
            $table->unsignedInteger('group_size_max')->nullable();
            $table->json('trip_schedule')->nullable();
            $table->text('meeting_point')->nullable();
            $table->string('best_season_from')->nullable();
            $table->string('best_season_to')->nullable();
            $table->json('catering')->nullable();
            $table->string('best_arrival_options')->nullable();
            $table->string('arrival_day')->nullable();

            // Boat information
            $table->string('boat_type')->nullable();
            $table->json('boat_features')->nullable();
            $table->text('boat_information')->nullable();

            // Accommodation & logistics
            $table->text('accommodation_description')->nullable();
            $table->string('accommodation_type')->nullable();
            $table->json('room_types')->nullable();
            $table->string('distance_to_water')->nullable();
            $table->string('nearest_airport')->nullable();

            // Provider
            $table->string('provider_name')->nullable();
            $table->string('provider_photo')->nullable();
            $table->text('provider_experience')->nullable();
            $table->text('provider_certifications')->nullable();
            $table->string('boat_staff')->nullable();
            $table->json('guide_languages')->nullable();

            // Description & itinerary
            $table->longText('description')->nullable();
            $table->json('trip_highlights')->nullable();

            // Included & excluded
            $table->json('included')->nullable();
            $table->json('excluded')->nullable();

            // Additional info toggles
            $table->json('additional_info')->nullable();

            // Pricing
            $table->text('cancellation_policy')->nullable();
            $table->decimal('price_per_person', 10, 2)->nullable();
            $table->decimal('price_single_room_addition', 10, 2)->nullable();
            $table->text('downpayment_policy')->nullable();

            // Meta
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
}


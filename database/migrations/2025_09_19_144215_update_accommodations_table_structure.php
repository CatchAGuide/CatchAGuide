<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAccommodationsTableStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Add new JSON columns for organized data structure
            $table->json('amenities')->nullable()->after('bed_types');
            $table->json('kitchen_equipment')->nullable()->after('amenities');
            $table->json('bathroom_amenities')->nullable()->after('kitchen_equipment');
            $table->json('policies')->nullable()->after('bathroom_amenities');
            $table->json('rental_conditions')->nullable()->after('policies');
            $table->json('per_person_pricing')->nullable()->after('rental_conditions');
            
            // Add price_type column
            $table->string('price_type')->nullable()->after('per_person_pricing');
            
            // Remove old boolean columns that are now part of JSON structures
            $table->dropColumn([
                'living_room',
                'dining_room_or_area',
                'terrace',
                'garden',
                'swimming_pool',
                'refrigerator_freezer',
                'oven',
                'stove_or_ceramic_hob',
                'microwave',
                'dishwasher',
                'coffee_machine',
                'cookware_and_dishes',
                'washing_machine',
                'dryer',
                'separate_laundry_room',
                'freezer_room',
                'filleting_house',
                'wifi_or_internet',
                'bed_linen_included',
                'utilities_included',
                'pets_allowed',
                'smoking_allowed',
                'reception_available',
                'rental_includes',
                'number_of_beds' // This is now part of bed_types JSON
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Drop new JSON columns
            $table->dropColumn([
                'amenities',
                'kitchen_equipment',
                'bathroom_amenities',
                'policies',
                'rental_conditions',
                'per_person_pricing',
                'price_type'
            ]);
            
            // Restore old boolean columns
            $table->boolean('living_room')->default(false);
            $table->boolean('dining_room_or_area')->default(false);
            $table->boolean('terrace')->default(false);
            $table->boolean('garden')->default(false);
            $table->boolean('swimming_pool')->default(false);
            $table->boolean('refrigerator_freezer')->default(false);
            $table->boolean('oven')->default(false);
            $table->boolean('stove_or_ceramic_hob')->default(false);
            $table->boolean('microwave')->default(false);
            $table->boolean('dishwasher')->default(false);
            $table->boolean('coffee_machine')->default(false);
            $table->boolean('cookware_and_dishes')->default(false);
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
            $table->json('rental_includes')->nullable();
            $table->integer('number_of_beds')->nullable();
        });
    }
}

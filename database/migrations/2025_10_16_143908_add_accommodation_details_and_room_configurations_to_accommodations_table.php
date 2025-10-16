<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccommodationDetailsAndRoomConfigurationsToAccommodationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->json('accommodation_details')->nullable()->after('bathroom_amenities');
            $table->json('room_configurations')->nullable()->after('accommodation_details');
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
            $table->dropColumn(['accommodation_details', 'room_configurations']);
        });
    }
}

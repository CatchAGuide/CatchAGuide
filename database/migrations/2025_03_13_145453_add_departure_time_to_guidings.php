<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartureTimeToGuidings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->string('desc_departure_time')->nullable()->after('desc_starting_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn('desc_departure_time');
        });
    }
}

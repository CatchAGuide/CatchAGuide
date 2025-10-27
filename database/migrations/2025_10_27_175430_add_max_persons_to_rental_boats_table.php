<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxPersonsToRentalBoatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_boats', function (Blueprint $table) {
            $table->integer('max_persons')->nullable()->after('boat_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rental_boats', function (Blueprint $table) {
            $table->dropColumn('max_persons');
        });
    }
}

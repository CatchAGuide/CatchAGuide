<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampRentalBoatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('camp_rental_boat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('camp_id');
            $table->unsignedBigInteger('rental_boat_id');
            $table->timestamps();
            
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
            $table->foreign('rental_boat_id')->references('id')->on('rental_boats')->onDelete('cascade');
            $table->unique(['camp_id', 'rental_boat_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('camp_rental_boat');
    }
}

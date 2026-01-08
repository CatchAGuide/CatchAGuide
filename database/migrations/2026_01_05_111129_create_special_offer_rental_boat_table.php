<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialOfferRentalBoatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_offer_rental_boat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('special_offer_id');
            $table->unsignedBigInteger('rental_boat_id');
            $table->timestamps();
            
            $table->foreign('special_offer_id')->references('id')->on('special_offers')->onDelete('cascade');
            $table->foreign('rental_boat_id')->references('id')->on('rental_boats')->onDelete('cascade');
            $table->unique(['special_offer_id', 'rental_boat_id'], 'so_rb_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_offer_rental_boat');
    }
}

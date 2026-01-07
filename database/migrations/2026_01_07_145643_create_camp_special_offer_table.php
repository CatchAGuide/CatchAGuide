<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampSpecialOfferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('camp_special_offer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('camp_id');
            $table->unsignedBigInteger('special_offer_id');
            $table->timestamps();
            
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
            $table->foreign('special_offer_id')->references('id')->on('special_offers')->onDelete('cascade');
            $table->unique(['camp_id', 'special_offer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('camp_special_offer');
    }
}

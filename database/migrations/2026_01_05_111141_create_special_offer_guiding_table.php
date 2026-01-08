<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialOfferGuidingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_offer_guiding', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('special_offer_id');
            $table->unsignedBigInteger('guiding_id');
            $table->timestamps();
            
            $table->foreign('special_offer_id')->references('id')->on('special_offers')->onDelete('cascade');
            $table->foreign('guiding_id')->references('id')->on('guidings')->onDelete('cascade');
            $table->unique(['special_offer_id', 'guiding_id'], 'so_guide_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_offer_guiding');
    }
}

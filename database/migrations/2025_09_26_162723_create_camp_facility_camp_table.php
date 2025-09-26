<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampFacilityCampTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('camp_facility_camp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('camp_id');
            $table->unsignedBigInteger('camp_facility_id');
            $table->timestamps();
            
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
            $table->foreign('camp_facility_id')->references('id')->on('camp_facilities')->onDelete('cascade');
            $table->unique(['camp_id', 'camp_facility_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('camp_facility_camp');
    }
}

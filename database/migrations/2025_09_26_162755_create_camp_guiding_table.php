<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampGuidingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('camp_guiding', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('camp_id');
            $table->unsignedBigInteger('guiding_id');
            $table->timestamps();
            
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
            $table->foreign('guiding_id')->references('id')->on('guidings')->onDelete('cascade');
            $table->unique(['camp_id', 'guiding_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('camp_guiding');
    }
}

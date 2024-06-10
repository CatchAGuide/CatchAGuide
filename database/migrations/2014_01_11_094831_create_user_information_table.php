<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInformationTable extends Migration
{
    public function up()
    {
        Schema::create('user_information', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->date('birthday')->nullable();
            $table->string('address')->nullable();
            $table->string('address_number')->nullable();
            $table->string('postal')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->longText('about_me')->nullable();
            $table->longText('languages')->nullable();
            $table->string('favorite_fish')->nullable();
            $table->integer('fishing_start_year')->nullable();
            $table->string('proof_of_identity_file_path')->nullable();
            $table->string('fishing_permit_file_path')->nullable();
            $table->boolean('request_as_guide')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_information');
    }
}

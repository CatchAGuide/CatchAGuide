<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_guests', function (Blueprint $table) {
            $table->id();
            $table->string('salutation')->nullable();
            $table->string('title')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('address');
            $table->string('postal');
            $table->string('city');
            $table->string('country')->default('Deutschland');
            $table->string('phone');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_guests');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuidingPricesTable extends Migration
{
    public function up()
    {
        Schema::create('guiding_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guiding_id');
            $table->integer('guest_count')->nullable();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('guiding_id')->references('id')->on('guidings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('guiding_prices');
    }
}
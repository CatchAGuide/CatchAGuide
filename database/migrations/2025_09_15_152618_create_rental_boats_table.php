<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentalBoatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_boats', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail_path')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('location');
            $table->string('city');
            $table->string('country');
            $table->string('region')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('boat_type');
            $table->text('desc_of_boat');
            $table->text('requirements')->nullable();
            $table->json('boat_information')->nullable();
            $table->json('boat_extras')->nullable();
            $table->string('price_type');
            $table->json('prices');
            $table->json('pricing_extra')->nullable();
            $table->json('inclusions')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'user_id']);
            $table->index(['city', 'country']);
            $table->index('boat_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rental_boats');
    }
}

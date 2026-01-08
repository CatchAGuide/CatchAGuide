<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('whats_included')->nullable(); // Tagify/free text data
            $table->json('pricing')->nullable(); // Pricing structure similar to accommodations/rental boats
            $table->string('price_type')->nullable(); // e.g., 'per_person', 'per_night', 'per_week', etc.
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_offers');
    }
}

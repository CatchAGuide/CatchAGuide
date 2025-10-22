<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('camps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description_camp');
            $table->text('description_area');
            $table->text('description_fishing');
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('distance_to_store')->nullable();
            $table->string('distance_to_nearest_town')->nullable();
            $table->string('distance_to_airport')->nullable();
            $table->string('distance_to_ferry_port')->nullable();
            $table->text('policies_regulations')->nullable();
            $table->text('best_travel_times')->nullable();
            $table->text('travel_information')->nullable();
            $table->text('extras')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->json('gallery_images')->nullable();
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
        Schema::dropIfExists('camps');
    }
}

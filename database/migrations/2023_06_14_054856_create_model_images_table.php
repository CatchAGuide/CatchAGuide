<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id')->index();
            $table->string('model_type', 32)->index();
            $table->string('image_name', 32)->nullable()->default('')->index();
            $table->string('image_size', 32)->nullable()->default('')->index();
            $table->string('image_url', 255)->nullable()->default('');
            $table->unsignedTinyInteger("image_exists")->nullable()->default(0)->index();
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
        Schema::dropIfExists('model_images');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_countries', function (Blueprint $table) {
            $table->id();
            $table->string('language');
            $table->string('title');
            $table->string('sub_title');
            $table->text('introduction')->nullable();
            $table->longText('content')->nullable();
            $table->json('filters')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_countries');
    }
}

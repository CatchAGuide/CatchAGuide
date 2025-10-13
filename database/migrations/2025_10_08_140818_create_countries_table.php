<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Base name (can be in default language)
            $table->string('slug')->unique();
            $table->string('countrycode')->nullable();
            $table->json('filters')->nullable();
            $table->text('thumbnail_path')->nullable();
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
        Schema::dropIfExists('c_countries');
    }
}

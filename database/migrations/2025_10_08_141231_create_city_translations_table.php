<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_city_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('c_cities')->onDelete('cascade');
            $table->string('language', 2); // 'en', 'de', etc.
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->text('introduction')->nullable();
            $table->longText('content')->nullable();
            $table->string('fish_avail_title')->nullable();
            $table->text('fish_avail_intro')->nullable();
            $table->string('size_limit_title')->nullable();
            $table->text('size_limit_intro')->nullable();
            $table->string('time_limit_title')->nullable();
            $table->text('time_limit_intro')->nullable();
            $table->string('faq_title')->nullable();
            $table->timestamps();
            
            // Ensure one translation per language per city
            $table->unique(['city_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_city_translations');
    }
}

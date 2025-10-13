<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('c_countries')->onDelete('cascade');
            $table->foreignId('region_id')->nullable()->constrained('c_regions')->onDelete('cascade');
            $table->string('name'); // Base name (can be in default language)
            $table->string('slug');
            $table->json('filters')->nullable();
            $table->text('thumbnail_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Unique slug per region (or country if no region)
            $table->unique(['country_id', 'region_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_cities');
    }
}

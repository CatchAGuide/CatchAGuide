<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLocationBoundariesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('location_boundaries')) {
            Schema::dropIfExists('location_boundaries');
        }

        Schema::create('location_boundaries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'country' or 'city'
            $table->string('country_code', 2)->nullable();
            $table->timestamps();
        });

        // Add geometry column using raw SQL for better MySQL spatial support
        DB::statement('ALTER TABLE location_boundaries ADD geometry GEOMETRY NOT NULL SRID 4326');
        DB::statement('CREATE SPATIAL INDEX location_boundaries_geometry_index ON location_boundaries(geometry)');
    }

    public function down()
    {
        Schema::dropIfExists('location_boundaries');
    }
}
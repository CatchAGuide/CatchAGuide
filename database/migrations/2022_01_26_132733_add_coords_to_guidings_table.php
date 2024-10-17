<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoordsToGuidingsTable extends Migration
{
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->float('lat')->nullable();
            $table->float('lng')->nullable();
        });
    }

    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
}

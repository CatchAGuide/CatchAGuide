<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageToDestinationFishTimeLimitsTable extends Migration
{
    public function up()
    {
        Schema::table('destination_fish_time_limits', function (Blueprint $table) {
            $table->string('language')->nullable();
        });
    }

    public function down()
    {
        Schema::table('destination_fish_time_limits', function (Blueprint $table) {
            $table->dropColumn([
                'language'
            ]);
        });
    }
}

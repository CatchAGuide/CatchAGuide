<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageToDestinationFishSizeLimitsTable extends Migration
{
    public function up()
    {
        Schema::table('destination_fish_size_limits', function (Blueprint $table) {
            $table->string('language')->nullable();
        });
    }

    public function down()
    {
        Schema::table('destination_fish_size_limits', function (Blueprint $table) {
            $table->dropColumn([
                'language'
            ]);
        });
    }
}

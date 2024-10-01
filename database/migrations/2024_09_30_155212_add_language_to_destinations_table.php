<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageToDestinationsTable extends Migration
{
    public function up()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('language')->nullable();
        });
    }

    public function down()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn([
                'language'
            ]);
        });
    }
}

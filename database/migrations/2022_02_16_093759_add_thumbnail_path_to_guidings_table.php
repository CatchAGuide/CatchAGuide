<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThumbnailPathToGuidingsTable extends Migration
{
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });
    }
}

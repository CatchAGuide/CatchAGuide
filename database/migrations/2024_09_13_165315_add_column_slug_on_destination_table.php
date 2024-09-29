<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSlugOnDestinationTable extends Migration
{
    public function up()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });
    }

    public function down()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn([
                'slug'
            ]);
        });
    }
}

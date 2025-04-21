<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreFieldsToGuidings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->string('weekday_availability')->nullable()->after('months');
            $table->longText('weekdays')->nullable()->after('weekday_availability');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn('weekday_availability');
            $table->dropColumn('weekdays');
        });
    }
}

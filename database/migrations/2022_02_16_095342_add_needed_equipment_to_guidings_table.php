<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNeededEquipmentToGuidingsTable extends Migration
{
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->string('needed_equipment')->nullable();
        });
    }

    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn('needed_equipment');
        });
    }
}

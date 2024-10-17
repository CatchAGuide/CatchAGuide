<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToGuidingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->bigInteger('fishing_type_id')->unsigned()->index();
            $table->bigInteger('fishing_from_id')->unsigned()->index();
            $table->bigInteger('equipment_status_id')->unsigned()->index();
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
            $table->dropColumn(['fishing_type_id','fishing_from_id','equipment_status_id']);
        });
    }
}

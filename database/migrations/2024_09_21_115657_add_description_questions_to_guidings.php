<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionQuestionsToGuidings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            //
            $table->text('desc_course_of_action')->nullable();
            $table->text('desc_meeting_point')->nullable();
            $table->text('desc_starting_time')->nullable();
            $table->text('desc_tour_unique')->nullable();
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
            //
            $table->dropColumn('desc_course_of_action');
            $table->dropColumn('desc_meeting_point');
            $table->dropColumn('desc_starting_time');
            $table->dropColumn('desc_tour_unique');
        });
    }
}

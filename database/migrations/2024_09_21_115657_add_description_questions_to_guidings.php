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
            if (!Schema::hasColumn('guidings', 'desc_course_of_action')) {
                $table->text('desc_course_of_action')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'desc_meeting_point')) {
                $table->text('desc_meeting_point')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'desc_starting_time')) {
                $table->text('desc_starting_time')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'desc_tour_unique')) {
                $table->text('desc_tour_unique')->nullable();
            }
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
            $table->dropColumn([
                'desc_course_of_action',
                'desc_meeting_point',
                'desc_starting_time',
                'desc_tour_unique'
            ]);
        });
    }
}

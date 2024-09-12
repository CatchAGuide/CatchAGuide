<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToGuidingsTable extends Migration
{
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->string('style_of_fishing')->nullable();
            $table->string('course_of_action')->nullable();
            $table->string('special_about')->nullable();
            $table->string('tour_unique')->nullable();
            $table->string('starting_time')->nullable();
            $table->string('private')->nullable();
            $table->string('allowed_booking_advance')->nullable();
            $table->string('booking_window')->nullable();
            $table->string('seasonal_trip')->nullable();
        });
    }

    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn([
                'style_of_fishing',
                'course_of_action',
                'special_about',
                'tour_unique',
                'starting_time',
                'private',
                'allowed_booking_advance',
                'booking_window',
                'seasonal_trip',
            ]);
        });
    }
}
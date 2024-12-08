<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToGuidingsTable extends Migration
{
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            if (!Schema::hasColumn('guidings', 'style_of_fishing')) {
                $table->string('style_of_fishing')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'course_of_action')) {
                $table->string('course_of_action')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'special_about')) {
                $table->string('special_about')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'tour_unique')) {
                $table->string('tour_unique')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'starting_time')) {
                $table->string('starting_time')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'private')) {
                $table->string('private')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'allowed_booking_advance')) {
                $table->string('allowed_booking_advance')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'booking_window')) {
                $table->string('booking_window')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'seasonal_trip')) {
                $table->string('seasonal_trip')->nullable();
            }
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
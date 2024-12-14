<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToGuidingsTableNew extends Migration
{
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            if (!Schema::hasColumn('guidings', 'boat_type')) {
                $table->string('boat_type')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'additional_info')) {
                $table->json('additional_info')->nullable();
            }
            if (!Schema::hasColumn('guidings', 'tour_type')) {
                $table->string('tour_type');
            }
            if (!Schema::hasColumn('guidings', 'months')) {
                $table->string('months')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn(['boat_type', 'additional_info', 'tour_type', 'months']);
        });
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToGuidingsTableNew extends Migration
{
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->boolean('is_boat')->default(false);
            $table->string('boat_type')->nullable();
            $table->json('additional_info')->nullable();
            $table->string('tour_type');
            $table->string('months')->nullable();
        });
    }

    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropColumn(['is_boat', 'boat_type', 'additional_info', 'tour_type', 'months']);
        });
    }
}
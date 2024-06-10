<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuideTypeColumnOnGuidingRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guiding_requests', function (Blueprint $table) {
            $table->string('guide_type')->nullable();
            $table->string('fishing_duration')->nullable();
            $table->string('days_of_fishing')->nullable();
            $table->string('from_date')->nullable();
            $table->string('to_date')->nullable();
            $table->string('rentaboat')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guiding_requests', function (Blueprint $table) {
            $table->dropColumn('guide_type');
            $table->dropColumn('fishing_duration');
            $table->dropColumn('days_of_fishing');
            $table->dropColumn('from_date');
            $table->dropColumn('to_date');
            $table->dropColumn('rentaboat');
        });
    }
}

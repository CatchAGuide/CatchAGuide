<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDescriptionFieldToTextForVacations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vacation_guidings', function (Blueprint $table) {
            $table->text('description')->change();
            $table->text('title')->after('vacation_id')->nullable();
        });
        Schema::table('vacation_packages', function (Blueprint $table) {
            $table->text('description')->change();
            $table->text('title')->after('vacation_id')->nullable();
        });
        Schema::table('vacation_accommodations', function (Blueprint $table) {
            $table->text('description')->change();
            $table->text('title')->after('vacation_id')->nullable();
        });
        Schema::table('vacation_boats', function (Blueprint $table) {
            $table->text('description')->change();
            $table->text('title')->after('vacation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacation_guidings', function (Blueprint $table) {
            $table->string('description')->change();
            $table->dropColumn('title');
        });
        Schema::table('vacation_packages', function (Blueprint $table) {
            $table->string('description')->change();
            $table->dropColumn('title');
        });
        Schema::table('vacation_accommodations', function (Blueprint $table) {
            $table->string('description')->change();
            $table->dropColumn('title');
        });
        Schema::table('vacation_boats', function (Blueprint $table) {
            $table->string('description')->change();
            $table->dropColumn('title');
        });
    }
}

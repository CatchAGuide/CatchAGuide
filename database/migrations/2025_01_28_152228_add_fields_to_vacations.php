<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToVacations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vacations', function (Blueprint $table) {
            if (Schema::hasColumn('vacations', 'has_boat')) {
                $table->dropColumn('has_boat');
            }
            if (Schema::hasColumn('vacations', 'has_guiding')) {
                $table->dropColumn('has_guiding'); 
            }
            $table->boolean('has_boat')->default(false)->after('disability_friendly');
            $table->boolean('has_guiding')->default(false)->after('has_boat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropColumn('has_boat');
            $table->dropColumn('has_guiding');
        });
    }
}

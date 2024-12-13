<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameGuidingInclusionstoinclussions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('guidings', 'inclusions')) {
            Schema::table('guidings', function (Blueprint $table) {
                $table->renameColumn('inclusions', 'inclussions');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->renameColumn('inclussions', 'inclusions');
        });
    }
}

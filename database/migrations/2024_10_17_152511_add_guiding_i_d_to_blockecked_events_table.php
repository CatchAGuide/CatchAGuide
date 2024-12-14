<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuidingIDToBlockeckedEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blocked_events', function (Blueprint $table) {
            if (Schema::hasColumn('blocked_events', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('blocked_events', 'guiding_id')) {
                $table->dropColumn('guiding_id');
            }
        });
        Schema::table('blocked_events', function (Blueprint $table) {
            $table->unsignedBigInteger('guiding_id')->after('user_id')->nullable();
            $table->foreign('guiding_id')->references('id')->on('guidings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blocked_events', function (Blueprint $table) {
            $table->dropColumn([
                'guiding_id'
            ]);
        });
    }
}

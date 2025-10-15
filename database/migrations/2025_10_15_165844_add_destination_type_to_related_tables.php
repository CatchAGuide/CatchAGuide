<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDestinationTypeToRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'destination_faqs',
            'destination_fish_charts',
            'destination_fish_size_limits',
            'destination_fish_time_limits'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'destination_type')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('destination_type', 50)->nullable()->after('destination_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'destination_faqs',
            'destination_fish_charts',
            'destination_fish_size_limits',
            'destination_fish_time_limits'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'destination_type')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('destination_type');
                });
            }
        }
    }
}

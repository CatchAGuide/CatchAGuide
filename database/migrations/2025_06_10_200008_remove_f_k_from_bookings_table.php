<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFKFromBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the existing foreign key constraint to blocked_events table
            $table->dropForeign(['blocked_event_id']);
            
            // Add new foreign key constraint to calendar_schedule table
            $table->foreign('blocked_event_id')->references('id')->on('calendar_schedule')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the foreign key constraint to calendar_schedule table
            $table->dropForeign(['blocked_event_id']);
            
            // Restore the original foreign key constraint to blocked_events table
            $table->foreign('blocked_event_id')->references('id')->on('blocked_events')->onDelete('set null');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            // Check if the foreign key constraint exists before trying to drop it
            $foreignKeys = $this->getForeignKeys('bookings', 'blocked_event_id');
            
            if (!empty($foreignKeys)) {
                // Drop the existing foreign key constraint to blocked_events table
                $table->dropForeign(['blocked_event_id']);
            }
            
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
            // Check if the foreign key constraint exists before trying to drop it
            $foreignKeys = $this->getForeignKeys('bookings', 'blocked_event_id');
            
            if (!empty($foreignKeys)) {
                // Drop the foreign key constraint to calendar_schedule table
                $table->dropForeign(['blocked_event_id']);
            }
            
            // Restore the original foreign key constraint to blocked_events table
            $table->foreign('blocked_event_id')->references('id')->on('blocked_events')->onDelete('set null');
        });
    }

    /**
     * Get foreign key constraints for a specific column
     *
     * @param string $table
     * @param string $column
     * @return array
     */
    private function getForeignKeys($table, $column)
    {
        return DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ? 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);
    }
}

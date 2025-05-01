<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guidings', function (Blueprint $table) {
            // Add indexes for commonly filtered columns
            $table->index('status', 'idx_guidings_status');
            $table->index('duration_type', 'idx_guidings_duration_type');
            $table->index('max_guests', 'idx_guidings_max_guests');
            $table->index('price', 'idx_guidings_price');
            $table->index('created_at', 'idx_guidings_created_at');
            $table->index('user_id', 'idx_guidings_user_id');
            
            // Add index for location searches
            $table->index(['lat', 'lng'], 'idx_guidings_location');
            
            // Add index for slug searches
            $table->index('slug', 'idx_guidings_slug');
            
            // Add index for JSON columns if your MySQL version supports it (5.7+)
            // These are commented out by default as they may not be supported
            // $table->index('target_fish', 'idx_guidings_target_fish');
            // $table->index('fishing_methods', 'idx_guidings_fishing_methods');
            // $table->index('water_types', 'idx_guidings_water_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guidings', function (Blueprint $table) {
            $table->dropIndex('idx_guidings_status');
            $table->dropIndex('idx_guidings_duration_type');
            $table->dropIndex('idx_guidings_max_guests');
            $table->dropIndex('idx_guidings_price');
            $table->dropIndex('idx_guidings_created_at');
            $table->dropIndex('idx_guidings_user_id');
            $table->dropIndex('idx_guidings_location');
            $table->dropIndex('idx_guidings_slug');
            
            // Drop JSON indexes if they were created
            // $table->dropIndex('idx_guidings_target_fish');
            // $table->dropIndex('idx_guidings_fishing_methods');
            // $table->dropIndex('idx_guidings_water_types');
        });
    }
}
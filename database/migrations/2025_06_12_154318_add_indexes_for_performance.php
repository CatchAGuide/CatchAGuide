<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForPerformance extends Migration
{
    /**
     * Check if an index exists on a table
     */
    private function indexExists($table, $indexName)
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
        return array_key_exists($indexName, $indexes);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use try-catch to handle existing indexes gracefully
        try {
            Schema::table('guidings', function (Blueprint $table) {
                // Location-based indexes for fast location filtering (exact names expected by diagnostic)
                $table->index(['status', 'city'], 'idx_guidings_status_city');
                $table->index(['status', 'country'], 'idx_guidings_status_country');
                $table->index(['status', 'region'], 'idx_guidings_status_region');
                $table->index(['status', 'city', 'country'], 'idx_guidings_status_city_country');
                $table->index(['status', 'city', 'region', 'country'], 'idx_guidings_status_location_full');
                
                // Spatial index for coordinate-based searches (exact name expected by diagnostic)
                $table->index(['lat', 'lng'], 'idx_guidings_lat_lng');
                
                // User-based index for guide searches
                $table->index(['user_id', 'status'], 'idx_guidings_user_status');
                
                // Price and sorting indexes (exact name expected by diagnostic)
                $table->index(['status', 'price'], 'idx_guidings_status_price');
                $table->index(['status', 'created_at'], 'idx_guidings_status_created');
                $table->index(['status', 'duration'], 'idx_guidings_status_duration');
                
                // Composite index for common filtering scenarios
                $table->index(['status', 'max_guests'], 'idx_guidings_status_guests');
                $table->index(['status', 'is_boat'], 'idx_guidings_status_boat');
            });
        } catch (\Exception $e) {
            // Log the error but don't fail the migration
            \Log::info('Some indexes may already exist: ' . $e->getMessage());
        }

        // Add indexes for related tables
        try {
            if (Schema::hasTable('targets')) {
                Schema::table('targets', function (Blueprint $table) {
                    $table->index(['id', 'name'], 'idx_targets_id_name');
                });
            }
        } catch (\Exception $e) {
            \Log::info('Targets index may already exist: ' . $e->getMessage());
        }

        try {
            if (Schema::hasTable('methods')) {
                Schema::table('methods', function (Blueprint $table) {
                    $table->index(['id', 'name'], 'idx_methods_id_name');
                });
            }
        } catch (\Exception $e) {
            \Log::info('Methods index may already exist: ' . $e->getMessage());
        }

        try {
            if (Schema::hasTable('waters')) {
                Schema::table('waters', function (Blueprint $table) {
                    $table->index(['id', 'name'], 'idx_waters_id_name');
                });
            }
        } catch (\Exception $e) {
            \Log::info('Waters index may already exist: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::table('guidings', function (Blueprint $table) {
                $table->dropIndex('idx_guidings_status_city');
                $table->dropIndex('idx_guidings_status_country');
                $table->dropIndex('idx_guidings_status_region');
                $table->dropIndex('idx_guidings_status_city_country');
                $table->dropIndex('idx_guidings_status_location_full');
                $table->dropIndex('idx_guidings_lat_lng');
                $table->dropIndex('idx_guidings_user_status');
                $table->dropIndex('idx_guidings_status_price');
                $table->dropIndex('idx_guidings_status_created');
                $table->dropIndex('idx_guidings_status_duration');
                $table->dropIndex('idx_guidings_status_guests');
                $table->dropIndex('idx_guidings_status_boat');
            });
        } catch (\Exception $e) {
            \Log::info('Error dropping guidings indexes: ' . $e->getMessage());
        }

        try {
            if (Schema::hasTable('targets')) {
                Schema::table('targets', function (Blueprint $table) {
                    $table->dropIndex('idx_targets_id_name');
                });
            }
        } catch (\Exception $e) {
            \Log::info('Error dropping targets index: ' . $e->getMessage());
        }

        try {
            if (Schema::hasTable('methods')) {
                Schema::table('methods', function (Blueprint $table) {
                    $table->dropIndex('idx_methods_id_name');
                });
            }
        } catch (\Exception $e) {
            \Log::info('Error dropping methods index: ' . $e->getMessage());
        }

        try {
            if (Schema::hasTable('waters')) {
                Schema::table('waters', function (Blueprint $table) {
                    $table->dropIndex('idx_waters_id_name');
                });
            }
        } catch (\Exception $e) {
            \Log::info('Error dropping waters index: ' . $e->getMessage());
        }
    }
}

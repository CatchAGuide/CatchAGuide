<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vacation listings created in the admin saved Auth::id() while the
     * employees guard was active. That number matches a guide in users,
     * so the listing was treated as theirs.
     */
    public function up(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('employee_id')->nullable()->after('user_id');
                $blueprint->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['user_id']);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('user_id')->nullable()->change();
                $blueprint->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        $employeeIds = DB::table('employees')->pluck('id');
        if ($employeeIds->isNotEmpty()) {
            foreach ($this->tables() as $table) {
                DB::table($table)
                    ->whereIn('user_id', $employeeIds)
                    ->update([
                        'employee_id' => DB::raw('user_id'),
                        'user_id' => null,
                    ]);
            }
        }

        $this->forgetAdminListingCaches();
    }

    public function down(): void
    {
        foreach ($this->tables() as $table) {
            DB::table($table)
                ->whereNull('user_id')
                ->whereNotNull('employee_id')
                ->update(['user_id' => DB::raw('employee_id')]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['employee_id']);
                $blueprint->dropColumn('employee_id');
                $blueprint->dropForeign(['user_id']);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('user_id')->nullable(false)->change();
                $blueprint->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        return ['rental_boats', 'camps', 'trips', 'accommodations', 'special_offers'];
    }

    private function forgetAdminListingCaches(): void
    {
        foreach ([
            'rental_boats' => 'rental_boat_',
            'camps' => 'camp_',
            'trips' => 'trip_',
            'accommodations' => 'accommodation_',
            'special_offers' => 'special_offer_',
        ] as $table => $prefix) {
            foreach (DB::table($table)->pluck('id') as $id) {
                Cache::forget($prefix.$id);
            }
        }

        foreach ([
            'rental_boats_list',
            'camps_list_15',
            'camps_list_20',
            'camps_list_25',
            'camps_list_50',
            'trips_list',
            'accommodations_list',
            'special_offers_list',
        ] as $key) {
            Cache::forget($key);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Review request for camp and trip bookings (sent by an admin, see VacationReviewService):
 * the token in the guest's review link and when the last request went out.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['camp_vacation_bookings', 'trip_bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('review_token', 64)->nullable()->unique()->after('status');
                $table->timestamp('review_requested_at')->nullable()->after('review_token');
            });
        }
    }

    public function down(): void
    {
        foreach (['camp_vacation_bookings', 'trip_bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['review_token']);
                $table->dropColumn(['review_token', 'review_requested_at']);
            });
        }
    }
};

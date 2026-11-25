<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guide Area › Bookings: a guide can cancel a confirmed booking with a reason (spec BK-07).
 * cancelled_by tells a guide's cancellation apart from the customer's and from an expired
 * request (which the expiry job also stores as `cancelled`).
 */
return new class extends Migration
{
    private const COLUMNS = ['cancel_reason', 'cancelled_by', 'cancelled_at'];

    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'cancel_reason')) {
                $table->string('cancel_reason', 40)->nullable()->after('status');
            }
            if (! Schema::hasColumn('bookings', 'cancelled_by')) {
                $table->string('cancelled_by', 20)->nullable()->after('cancel_reason');
            }
            if (! Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });
    }

    public function down(): void
    {
        $existing = array_values(array_filter(self::COLUMNS, fn (string $c) => Schema::hasColumn('bookings', $c)));

        if ($existing !== []) {
            Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn($existing));
        }
    }
};

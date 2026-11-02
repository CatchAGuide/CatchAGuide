<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a "done" request ended (App\Enums\DoneReason: booked / no_reply), for camp/vacation and
 * trip requests and contact requests. Null for open / in process and for requests closed
 * before this existed.
 */
return new class extends Migration
{
    private const TABLES = ['camp_vacation_bookings', 'trip_bookings', 'contact_submissions'];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            if (Schema::hasColumn($name, 'done_reason')) {
                continue;
            }
            Schema::table($name, function (Blueprint $table) {
                $table->string('done_reason', 20)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            if (! Schema::hasColumn($name, 'done_reason')) {
                continue;
            }
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('done_reason');
            });
        }
    }
};

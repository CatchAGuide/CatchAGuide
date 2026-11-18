<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guide Area › Calendar: where a block came from. `manual` = blocked by the guide, `ical` =
 * busy day imported from a private calendar (can't be unblocked in Catch A Guide, spec 7.6).
 * Older rows stay null and are treated as manual.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('calendar_schedule', 'source')) {
            return;
        }

        Schema::table('calendar_schedule', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('calendar_schedule', 'source')) {
            return;
        }

        Schema::table('calendar_schedule', fn (Blueprint $table) => $table->dropColumn('source'));
    }
};

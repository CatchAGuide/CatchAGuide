<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Follow-up to Phase 5 (docs/category-pages-data-consolidation-plan.md §17). The
 * 2026_08_21_160000 drop migration deliberately left destination_faqs in place, grouped
 * with destination_fish_charts/_size_limits/_time_limits under one "still open" umbrella.
 * That grouping no longer holds for this table specifically: all 498 destination_faqs rows
 * (402 originally typed + the 96 orphaned rows resolved via the legacy `destinations` table,
 * §17) are migrated into `faqs`, and a repo-wide check found zero remaining reads or writes
 * against `destination_faqs` anywhere in app/ — unlike the fish tables, which the admin
 * Country/Region/City controllers still actively read and write today.
 *
 * Rollback is a restore from a pre-migration backup, not this migration's down() — same
 * convention as 2026_08_21_160000.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('destination_faqs');
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'Irreversible: restore destination_faqs from a pre-migration database backup instead of rolling back this migration.'
        );
    }
};

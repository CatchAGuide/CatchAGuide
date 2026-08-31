<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 (decommission) — drops the legacy geo tables fully superseded by
 * category_entities/languages/faqs as of the Phase 3 write cutover and Phase 4
 * read cutover (see docs/category-pages-data-consolidation-plan.md §14-§15).
 *
 * Deliberately excludes destination_faqs (96 of 498 rows never triaged — plan §9
 * risk #11) and destination_fish_charts/_size_limits/_time_limits (still actively
 * written by the admin controllers, blocked on manual regulatory re-verification —
 * plan §9 risk #10). Those stay until their own manual worklists clear.
 *
 * Rollback is a restore from a pre-migration DB backup, not this migration's
 * down() — per the plan's own §10 testing/rollback section, Phase 5 rollback was
 * always meant to be "the tables aren't dropped until a verified backup exists",
 * not a schema-only reconstruction that would come back empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('c_country_translations');
        Schema::dropIfExists('c_region_translations');
        Schema::dropIfExists('c_city_translations');
        Schema::dropIfExists('c_cities');
        Schema::dropIfExists('c_regions');
        Schema::dropIfExists('c_countries');
        Schema::dropIfExists('destinations');
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'Irreversible: restore c_countries/c_regions/c_cities/c_*_translations/destinations '
            .'from a pre-Phase-5 database backup instead of rolling back this migration.'
        );
    }
};

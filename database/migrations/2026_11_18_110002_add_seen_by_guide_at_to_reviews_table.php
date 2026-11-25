<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guide Area › Reviews: NEW badge until the guide has seen a review (spec RV-02/RV-04).
 * Reviews that exist today count as seen (plan: data migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reviews', 'seen_by_guide_at')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->timestamp('seen_by_guide_at')->nullable()->after('is_automatic');
        });

        DB::table('reviews')->whereNull('seen_by_guide_at')->update(['seen_by_guide_at' => DB::raw('COALESCE(created_at, NOW())')]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('reviews', 'seen_by_guide_at')) {
            return;
        }

        Schema::table('reviews', fn (Blueprint $table) => $table->dropColumn('seen_by_guide_at'));
    }
};

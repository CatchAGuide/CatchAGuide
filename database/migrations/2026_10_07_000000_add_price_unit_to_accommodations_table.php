<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How an accommodation's nightly tier price is charged (offer builder spec §5.1 / §6.4):
 * per_night = the price is for the unit (existing tiers are "total price for N guests"),
 * per_person_night = the price is per guest and night.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->string('price_unit', 20)->default('per_night')->after('per_person_pricing');
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn('price_unit');
        });
    }
};

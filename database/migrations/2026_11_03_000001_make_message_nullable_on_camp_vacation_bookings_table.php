<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin › Bookings › Camps › manual create lets the team leave the guest's message empty
 * (the form rule is `nullable`), but the column was NOT NULL, so those saves failed with a 500.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->text('message')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('camp_vacation_bookings')->whereNull('message')->update(['message' => '']);

        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->text('message')->nullable(false)->change();
        });
    }
};

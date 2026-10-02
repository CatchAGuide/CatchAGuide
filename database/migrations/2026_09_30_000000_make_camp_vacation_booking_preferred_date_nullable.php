<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Camp checkout treats arrival as optional, so a request can be stored without a date.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->date('preferred_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->date('preferred_date')->nullable(false)->change();
        });
    }
};

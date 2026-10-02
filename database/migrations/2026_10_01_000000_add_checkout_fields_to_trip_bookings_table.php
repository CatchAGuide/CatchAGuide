<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured selection from the trip checkout (pages/trip-checkout). Nullable so rows created
 * by the older contact-modal flow and the admin's manual entry stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            // End of the guest's preferred travel window; null when a fixed departure was picked.
            $table->date('preferred_date_to')->nullable()->after('preferred_date');
            $table->decimal('estimated_total', 10, 2)->nullable()->after('number_of_persons');
            $table->string('currency', 3)->nullable()->after('estimated_total');
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->unsignedBigInteger('user_id')->nullable()->after('email');
            $table->string('language', 5)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_date_to', 'estimated_total', 'currency', 'first_name', 'last_name', 'user_id', 'language',
            ]);
        });
    }
};

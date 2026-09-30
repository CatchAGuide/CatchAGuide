<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured selection from the camp checkout (pages/camp-checkout). Nullable so rows created
 * by the older contact-modal flow and the admin's manual entry stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('nights')->nullable()->after('preferred_date');
            $table->unsignedBigInteger('accommodation_id')->nullable()->after('nights');
            $table->unsignedBigInteger('rental_boat_id')->nullable()->after('accommodation_id');
            $table->unsignedBigInteger('guiding_id')->nullable()->after('rental_boat_id');
            $table->unsignedBigInteger('special_offer_id')->nullable()->after('guiding_id');
            $table->decimal('estimated_total', 10, 2)->nullable()->after('special_offer_id');
            $table->string('currency', 3)->nullable()->after('estimated_total');
            $table->json('price_breakdown')->nullable()->after('currency');
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->unsignedBigInteger('user_id')->nullable()->after('email');
            $table->string('language', 5)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('camp_vacation_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'nights', 'accommodation_id', 'rental_boat_id', 'guiding_id', 'special_offer_id',
                'estimated_total', 'currency', 'price_breakdown', 'first_name', 'last_name', 'user_id', 'language',
            ]);
        });
    }
};

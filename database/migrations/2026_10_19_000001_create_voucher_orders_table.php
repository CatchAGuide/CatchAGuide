<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tours bought as gift vouchers. Kept apart from `bookings`: there is no date, no guide
 * acceptance and no status. An order is final once created; payment, delivery and redemption
 * happen between guide and buyer. Tour, price and commission are snapshots, so later edits to
 * the tour or the guide's settings don't change orders already placed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_orders', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_code', 20)->unique();
            $table->unsignedBigInteger('guiding_id')->index();
            $table->unsignedBigInteger('guide_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('tour_title_snapshot');
            $table->unsignedSmallInteger('number_of_guests');
            $table->decimal('price_total', 10, 2);
            $table->char('currency', 3)->default('EUR');
            $table->string('recipient_names', 100);
            $table->string('sender_name', 100)->nullable();
            $table->string('personal_message', 200)->nullable();
            $table->string('buyer_first_name', 100);
            $table->string('buyer_last_name', 100);
            $table->string('buyer_email');
            $table->string('buyer_phone_country_code', 10)->nullable();
            $table->string('buyer_phone', 30);
            // App\Enums\VoucherDesign, copied from the guide at purchase.
            $table->string('design_type', 20);
            $table->string('pdf_path')->nullable();
            $table->string('language', 5);
            $table->date('valid_until');
            $table->decimal('commission_rate', 6, 4);
            $table->decimal('commission_amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_orders');
    }
};

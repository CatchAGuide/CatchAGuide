<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest booking access, Phase 1 (plan "Guest Booking Access", 18 Nov 2026): one link per
 * booking (/b/{token}) for tours, camps, trips and vouchers, plus a log of what happened to
 * each link. The log keeps no IP address or browser (follow-up F5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_access_tokens', function (Blueprint $table) {
            $table->id();
            // tour | camp | trip | voucher (App\Enums\GuestBookingType)
            $table->string('bookable_type', 16);
            $table->unsignedBigInteger('bookable_id');
            // Lowercased email the booking was made with; links are only ever sent there.
            $table->string('email')->nullable();
            // sha256 of the token, for the lookup. The encrypted copy lets every later email
            // repeat the same link, which a hash alone can't.
            $table->char('token_hash', 64)->unique();
            $table->text('token_encrypted');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['bookable_type', 'bookable_id']);
            $table->index('email');
        });

        Schema::create('booking_access_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_access_token_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('bookable_type', 16)->nullable();
            $table->unsignedBigInteger('bookable_id')->nullable();
            // created | sent | opened | revoked
            $table->string('event', 16);
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['bookable_type', 'bookable_id']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_access_events');
        Schema::dropIfExists('booking_access_tokens');
    }
};

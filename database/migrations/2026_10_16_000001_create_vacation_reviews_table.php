<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest reviews of camps and trips. Kept apart from `reviews` (tour reviews), whose scores and
 * averages are tied to a guide and a guiding.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_reviews', function (Blueprint $table) {
            $table->id();
            // CampVacationBooking or TripBooking; one review per booking.
            $table->morphs('reviewable');
            $table->unique(['reviewable_type', 'reviewable_id']);
            // The booking's source (camp, vacation, trip) so reviews can be listed per listing.
            $table->string('listing_type', 20);
            $table->unsignedBigInteger('listing_id');
            $table->index(['listing_type', 'listing_id']);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('overall_score');
            $table->unsignedTinyInteger('accommodation_score');
            $table->unsignedTinyInteger('fishing_score');
            $table->unsignedTinyInteger('value_score');
            $table->decimal('grandtotal_score', 4, 2);
            $table->text('comment');
            $table->string('language', 5)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_reviews');
    }
};

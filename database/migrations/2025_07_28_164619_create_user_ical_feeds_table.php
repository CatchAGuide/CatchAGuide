<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_ical_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // User-defined name for the feed
            $table->string('feed_token', 64)->unique(); // Unique token for the feed URL
            $table->string('otp_secret', 32); // Secret for OTP generation
            $table->enum('feed_type', ['bookings_only', 'all_events', 'custom_schedule'])->default('bookings_only');
            $table->json('feed_settings')->nullable(); // Additional settings like date range, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_accessed_at')->nullable();
            $table->integer('access_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // Optional expiration
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['feed_token']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ical_feeds');
    }
};

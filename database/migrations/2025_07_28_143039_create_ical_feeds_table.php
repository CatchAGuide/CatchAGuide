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
        Schema::create('ical_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // User-defined name for the feed
            $table->text('feed_url'); // The iCal feed URL
            $table->enum('sync_type', ['bookings_only', 'all_events'])->default('bookings_only');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_successful_sync_at')->nullable();
            $table->integer('sync_frequency_hours')->default(24); // How often to sync
            $table->json('sync_settings')->nullable(); // Additional sync settings
            $table->text('last_error')->nullable(); // Last sync error message
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['last_sync_at']);
            $table->index(['sync_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ical_feeds');
    }
};

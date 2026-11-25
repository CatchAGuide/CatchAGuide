<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Find my booking" sign-in links (plan "Guest Booking Access", Part 2). A link is single use
 * and valid for 30 minutes; using it starts a 7-day sign-in for that email (Q4), which this
 * row keeps, so an admin or "Sign out" can end it. Rate limits live in the cache.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_login_links', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->index();
            $table->char('token_hash', 64)->unique();
            $table->string('locale', 5)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('session_expires_at')->nullable();
            $table->timestamp('signed_out_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_login_links');
    }
};

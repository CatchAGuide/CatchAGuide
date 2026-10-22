<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reviews an admin enters by hand (Admin › Reviews › Add camp/trip review): they may have no
 * booking behind them, so the booking morph becomes optional and the guest name is stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacation_reviews', function (Blueprint $table) {
            $table->string('reviewable_type')->nullable()->change();
            $table->unsignedBigInteger('reviewable_id')->nullable()->change();
            $table->string('guest_name', 120)->nullable()->after('user_id');
            $table->boolean('is_manual')->default(false)->after('language');
        });
    }

    public function down(): void
    {
        Schema::table('vacation_reviews', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'is_manual']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status history for camp/vacation and trip requests (Admin › Bookings › Camps / Trips): one row
 * per change, with the employee who made it. Null employee means the system changed it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_changes', function (Blueprint $table) {
            $table->id();
            $table->morphs('bookable');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            // Why the status changed when it was not the status menu: created, offer_created.
            $table->string('reason', 30)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_changes');
    }
};

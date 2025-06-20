<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablCalendarSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_schedule', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['tour_request', 'vacation_request', 'tour_schedule', 'vacation_schedule', 'custom_schedule']);
            $table->date('date');
            $table->string('note')->nullable();
            $table->unsignedBigInteger('guiding_id')->nullable();
            $table->unsignedBigInteger('vacation_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['guiding_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['type', 'date']);
            
            // Add foreign key constraints (optional, but recommended)
            $table->foreign('guiding_id')->references('id')->on('guidings')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_schedule');
    }
}

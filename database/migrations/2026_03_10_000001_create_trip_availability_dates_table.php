<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripAvailabilityDatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('trip_availability_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->date('departure_date');
            $table->unsignedInteger('spots_available')->default(0);
            $table->string('status')->default('available');
            $table->timestamps();

            $table->foreign('trip_id')
                ->references('id')
                ->on('trips')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_availability_dates');
    }
}


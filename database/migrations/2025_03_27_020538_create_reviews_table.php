<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('reviews');
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('comment')->nullable();
            $table->double('overall_score')->default(0);
            $table->double('guide_score')->default(0);
            $table->double('region_water_score')->default(0);
            $table->double('grandtotal_score')->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('guide_id')->constrained('users');
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('guiding_id')->constrained('guidings');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}

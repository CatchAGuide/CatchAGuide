<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vacation_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained('vacations')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration');
            $table->integer('number_of_persons');
            $table->enum('booking_type', ['package', 'custom']);
            
            // Optional relations
            $table->foreignId('package_id')->nullable()->constrained('vacation_packages')->nullOnDelete();
            $table->foreignId('accommodation_id')->nullable()->constrained('vacation_accommodations')->nullOnDelete();
            $table->foreignId('boat_id')->nullable()->constrained('vacation_boats')->nullOnDelete();
            $table->foreignId('guiding_id')->nullable()->constrained('vacation_guidings')->nullOnDelete();
            
            // Customer details
            $table->string('title');
            $table->string('name');
            $table->string('surname');
            $table->string('street');
            $table->string('post_code');
            $table->string('city');
            $table->string('country');
            $table->string('phone_country_code');
            $table->string('phone');
            $table->string('email');
            $table->text('comments')->nullable();
            
            // Additional fields
            $table->boolean('has_pets')->default(false);
            $table->json('extra_offers')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('pending');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vacation_bookings');
    }
};
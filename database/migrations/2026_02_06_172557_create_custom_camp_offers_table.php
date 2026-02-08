<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomCampOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_camp_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Optional name for the custom camp offer
            $table->enum('recipient_type', ['customer', 'manual'])->default('customer');
            $table->unsignedBigInteger('customer_id')->nullable(); // If recipient_type is 'customer'
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->unsignedBigInteger('camp_id')->nullable();
            $table->json('accommodation_ids')->nullable(); // Array of accommodation IDs
            $table->json('boat_ids')->nullable(); // Array of boat IDs
            $table->json('guiding_ids')->nullable(); // Array of guiding IDs
            $table->string('date_from')->nullable();
            $table->string('date_to')->nullable();
            $table->string('number_of_persons')->nullable();
            $table->string('price')->nullable();
            $table->text('additional_info')->nullable();
            $table->text('free_text')->nullable(); // Free text message
            $table->json('offers')->nullable(); // Array of multiple offers (if offers array was used)
            $table->string('locale', 2)->default('en');
            $table->unsignedBigInteger('created_by')->nullable(); // Admin user who created this offer
            $table->timestamp('sent_at')->nullable(); // When the offer was sent
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('customer_id');
            $table->index('recipient_email');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_camp_offers');
    }
}

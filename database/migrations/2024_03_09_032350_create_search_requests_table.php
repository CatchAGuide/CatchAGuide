<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_requests', function (Blueprint $table) {
            $table->id();
            $table->string('fishing_type');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('target_fish');
            $table->integer('number_of_guest');
            $table->boolean('is_best_fishing_time_recommendation')->default(0);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->boolean('is_guided')->default(0);
            $table->string('days_of_guiding')->nullable();
            $table->boolean('is_boat_rental')->default(0);
            $table->string('days_of_boat_rental')->nullable();
            $table->decimal('total_budget_to_spend',6,2)->nullable();
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('search_requests');
    }
}

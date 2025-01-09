<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVacationAccomodationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacation_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained('vacations');
            $table->string('description');
            $table->integer('capacity')->default(1);
            $table->json('dynamic_fields')->nullable();
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
        Schema::dropIfExists('vacation_accomodations');
    }
}

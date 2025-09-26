<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_boat_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // German name
            $table->string('name_en'); // English name
            $table->string('input_type')->default('text'); // text, number, textarea, etc.
            $table->string('placeholder')->nullable(); // German placeholder
            $table->string('placeholder_en')->nullable(); // English placeholder
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_boat_requirements');
    }
};
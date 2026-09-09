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
        Schema::create('vacation_testimonials', function (Blueprint $table) {
            $table->id();
            $table->text('quote');
            $table->string('author');
            $table->decimal('rating', 3, 1);
            $table->date('reviewed_on')->nullable();
            $table->string('listing_title')->nullable();
            $table->string('listing_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_testimonials');
    }
};

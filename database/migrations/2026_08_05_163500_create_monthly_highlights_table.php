<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_highlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month')->unique();
            $table->string('title_en');
            $table->string('title_de');
            $table->text('subtitle_en')->nullable();
            $table->text('subtitle_de')->nullable();
            $table->json('items')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_highlights');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guide profile tags: "Specialties" (kind = specialty) and "Good for" (kind = good_for).
 * Values from the suggestion list are stored as their key; custom values as typed (is_custom).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('guide_tags')) {
            return;
        }

        Schema::create('guide_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('value', 60);
            $table->boolean('is_custom')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'kind', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_tags');
    }
};

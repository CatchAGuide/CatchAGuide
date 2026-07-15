<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('terms_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terms_section_id')
                ->constrained('terms_sections')
                ->cascadeOnDelete();
            $table->string('language', 5);
            $table->string('title');
            $table->longText('content');
            $table->timestamps();

            $table->unique(['terms_section_id', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_section_translations');
        Schema::dropIfExists('terms_sections');
    }
};

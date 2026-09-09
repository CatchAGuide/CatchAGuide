<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('category_entities')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('category_entities')->nullOnDelete();
            $table->foreignId('target_id')->constrained('targets')->cascadeOnDelete();

            // Availability grid, same shape as today's destination_fish_charts.
            foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'] as $month) {
                $table->tinyInteger($month)->nullable();
            }

            $table->date('closed_from')->nullable();
            $table->date('closed_to')->nullable();
            $table->integer('min_size_cm')->nullable();
            $table->integer('max_size_cm')->nullable();
            $table->integer('bag_limit')->nullable();
            $table->text('licence_note')->nullable();

            // Not nullable: a regulation row is not considered verified without a citation.
            $table->text('source_url');
            $table->text('source_name');
            $table->date('verified_at');
            $table->text('verified_by');

            $table->timestamps();

            $table->index(['country_id', 'target_id']);
            $table->index(['region_id', 'target_id']);
            $table->index('target_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};

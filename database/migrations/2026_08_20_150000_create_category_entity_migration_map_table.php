<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_entity_migration_map', function (Blueprint $table) {
            $table->id();
            $table->string('old_table', 64);
            $table->unsignedBigInteger('old_id');
            $table->foreignId('new_id')->constrained('category_entities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['old_table', 'old_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_entity_migration_map');
    }
};

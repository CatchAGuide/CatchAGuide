<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_entities', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32); // 'country' | 'region' | 'city'
            $table->foreignId('parent_id')->nullable()->constrained('category_entities')->nullOnDelete();
            // Denormalized helpers so flat `WHERE country_id = ?` / `WHERE region_id = ?` reads (as used by
            // VacationDestinationRepository, CampListingRepository, TripListingRepository, filters, sitemap
            // contributors) stay single-column lookups instead of walking parent_id recursively.
            $table->foreignId('country_id')->nullable()->constrained('category_entities')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('category_entities')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('countrycode')->nullable();
            $table->json('filters')->nullable();
            $table->text('thumbnail_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index(['type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_entities');
    }
};

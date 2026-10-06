<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-edited versions of the offer builder's fixed customer texts (spec §8.5, P1): default
 * intros, email texts, signature line, thank-you message — per language. No row = the
 * default from resources/lang/{de,en}/sales.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_text_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50);
            $table->string('language', 2);
            $table->text('body');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            $table->unique(['key', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_text_templates');
    }
};

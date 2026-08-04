<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('reason');
            $table->text('description');
            $table->string('reported_url');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('status')->default('open');
            $table->text('admin_comment')->nullable();
            $table->string('locale', 10)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};

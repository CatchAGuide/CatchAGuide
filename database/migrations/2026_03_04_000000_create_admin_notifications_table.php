<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('level', 20)->default('info');
            $table->string('link')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Licences & certificates a guide uploads as proof. The files live on the private disk
 * (config media_storage.private_disk) and are only streamed to the owner and to admins.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('guide_certificates')) {
            return;
        }

        Schema::create('guide_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title', 120)->nullable();
            $table->string('file_disk', 40);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('in_review');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_certificates');
    }
};
